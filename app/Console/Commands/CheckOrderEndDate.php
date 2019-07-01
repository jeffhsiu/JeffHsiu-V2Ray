<?php

namespace App\Console\Commands;

use App\Models\Order\Order;
use App\Models\VPS\Server;
use App\Models\VPS\ServerLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOrderEndDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:check-enddate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check order end date, if end date expired, stop the docker.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::where('status', Order::STATUS_ENABLE)
            ->where('end_date', '<', Carbon::today())
            ->get();

        foreach ($orders as $order) {
            $server = Server::find($order->server_id);
            if ( !$server) {
                Log::error('Server dose not exists. order_id:' . $order->id . ', server_id:'.$order->server_id);
                continue;
            }

            $param['username'] = 'root';
            $param['password'] = $server->ssh_pwd;
            $param['ip'] = $server->ip;
            $param['port'] = $server->ssh_port;
            $param['docker_name'] = $order->docker_name;
            $param['docker_name_unit'] = substr($order->docker_name, 0, -2).(substr($order->docker_name, -2)+0);

            if (Order::where('server_id', $order->server_id)
                ->where('docker_name', $order->docker_name)
                ->where('status', Order::STATUS_ENABLE)
                ->where('end_date', '>=', Carbon::today())
                ->exists()) {
                // 如果還有在使用的訂單，docker重啟，重新計算流量
                $this->sshDockerAction($param, 'restart');

                // 伺服器操作記錄
                ServerLog::create([
                    'user_id' => ServerLog::USER_ID_SYSTEM,
                    'server_id' => $server->id,
                    'order_id' => $order->id,
                    'ip' => $server->ip,
                    'docker_name' => $order->docker_name,
                    'action' => ServerLog::ACTION_DOCKER_RESTART,
                    'reason' => 'Order end date expired. Recalculate net.'
                ]);

            } else {
                // 已經沒有在使用的訂單，docker停止
                $this->sshDockerAction($param, 'stop');

                // 伺服器操作記錄
                ServerLog::create([
                    'user_id' => ServerLog::USER_ID_SYSTEM,
                    'server_id' => $server->id,
                    'order_id' => $order->id,
                    'ip' => $server->ip,
                    'docker_name' => $order->docker_name,
                    'action' => ServerLog::ACTION_DOCKER_STOP,
                    'reason' => 'Order end date expired.'
                ]);

                // Wechat消息推送
                $text = $order->customer->name.'_使用日到期，Docker已暫停';
                $desp = sprintf(
                    'Customer: %s'.PHP_EOL.PHP_EOL.
                    'IP: %s'.PHP_EOL.PHP_EOL.
                    'Docker name: %s'.PHP_EOL.PHP_EOL.
                    'Action: %s'.PHP_EOL.PHP_EOL.
                    'Reason: %s',
                    $order->customer->name,
                    $order->server->ip,
                    $order->docker_name,
                    'Docker 停止',
                    '訂單使用日到期'
                );
                wechatPush($text, $desp);
            }

            // 訂單設為過期狀態
            $order->update(['status' => Order::STATUS_EXPIRED]);
        }
    }

    private function sshDockerAction($param, $action) {
        try {
            $connection = ssh2_connect($param['ip'], $param['port']);
            ssh2_auth_password($connection, $param['username'], $param['password']);

            ssh2_exec($connection, 'docker '.$action.' '.$param['docker_name'].' '.$param['docker_name_unit']);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('Docker '.$action.' failed. error: '.$exception->getMessage());
        }
    }
}
