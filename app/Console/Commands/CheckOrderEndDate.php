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
        // 取得過期的訂單
        $orders = Order::where('status', Order::STATUS_ENABLE)
            ->where('end_date', '<', Carbon::today())
            ->get();

        foreach ($orders as $order) {

            echo '訂單使用日到期，用戶:'.$order->customer->name.'，IP:'.$order->server->ip.'，Docker:'.$order->docker_name.PHP_EOL;

            $server = Server::find($order->server_id);
            if ( !$server) {
                Log::error('Server dose not exists. order_id:' . $order->id . ', server_id:'.$order->server_id);
                continue;
            }

            $param = array();
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
                $net = $this->sshDockerAction($param, 'restart');

                // 伺服器操作記錄
                ServerLog::create([
                    'user_id' => ServerLog::USER_ID_SYSTEM,
                    'server_id' => $server->id,
                    'order_id' => $order->id,
                    'ip' => $server->ip,
                    'docker_name' => $order->docker_name,
                    'action' => ServerLog::ACTION_DOCKER_RESTART,
                    'reason' => 'Order end date expired. Recalculate net.',
                    'net' => $net
                ]);

                echo 'Docker 已重啟'.PHP_EOL;

            } else {
                // 已經沒有在使用的訂單，docker停止
                $net = $this->sshDockerAction($param, 'stop');

                // 伺服器操作記錄
                ServerLog::create([
                    'user_id' => ServerLog::USER_ID_SYSTEM,
                    'server_id' => $server->id,
                    'order_id' => $order->id,
                    'ip' => $server->ip,
                    'docker_name' => $order->docker_name,
                    'action' => ServerLog::ACTION_DOCKER_STOP,
                    'reason' => 'Order end date expired.',
                    'net' => $net
                ]);

                // Wechat消息推送
                $text = $order->customer->name.'_訂單到期，Docker已停止';
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

                echo 'Docker 已停止'.PHP_EOL;
            }

            // 訂單設為過期狀態
            $order->update(['status' => Order::STATUS_EXPIRED]);
        }

        // 一個月以上的訂單，每30天 docker 重啟重新計算流量
        $orders = Order::where('status', Order::STATUS_ENABLE)
            ->where('start_date', '<', Carbon::today())
            ->whereRaw('MOD(DATEDIFF(NOW(), start_date), 30) = 0')
            ->get();

        foreach ($orders as $order) {

            echo '訂單流量重置，用戶:'.$order->customer->name.'，IP:'.$order->server->ip.'，Docker:'.$order->docker_name.PHP_EOL;

            $server = Server::find($order->server_id);
            if ( !$server) {
                Log::error('Server dose not exists. order_id:' . $order->id . ', server_id:'.$order->server_id);
                continue;
            }

            $param = array();
            $param['username'] = 'root';
            $param['password'] = $server->ssh_pwd;
            $param['ip'] = $server->ip;
            $param['port'] = $server->ssh_port;
            $param['docker_name'] = $order->docker_name;
            $param['docker_name_unit'] = substr($order->docker_name, 0, -2).(substr($order->docker_name, -2)+0);

            $net = $this->sshDockerAction($param, 'restart');

            // 伺服器操作記錄
            ServerLog::create([
                'user_id' => ServerLog::USER_ID_SYSTEM,
                'server_id' => $server->id,
                'order_id' => $order->id,
                'ip' => $server->ip,
                'docker_name' => $order->docker_name,
                'action' => ServerLog::ACTION_DOCKER_RESTART,
                'reason' => 'Order monthly due. Recalculate net.',
                'net' => $net
            ]);

            // Wechat消息推送
            $text = $order->customer->name.'_每月流量重置';
            $desp = sprintf(
                'Customer: %s'.PHP_EOL.PHP_EOL.
                'IP: %s'.PHP_EOL.PHP_EOL.
                'Docker name: %s'.PHP_EOL.PHP_EOL.
                'Action: %s'.PHP_EOL.PHP_EOL.
                'Reason: %s',
                $order->customer->name,
                $order->server->ip,
                $order->docker_name,
                'Docker 重啟',
                '訂單每月使用日到期，流量重新計算'
            );
            wechatPush($text, $desp);

            echo 'Docker 已重啟'.PHP_EOL;
        }
    }

    private function sshDockerAction($param, $action) {
        try {
            $connection = ssh2_connect($param['ip'], $param['port']);
            ssh2_auth_password($connection, $param['username'], $param['password']);

            // 取得操作前Docker流量
            $stream = ssh2_exec($connection, 'docker stats --no-stream --format "{{.NetIO}}" '.$param['docker_name']);
            stream_set_blocking($stream, true);
            $docker_stats_output = stream_get_contents($stream);
            $net = explode(' ', $docker_stats_output)[0];
            if ($net == 'Error') {
                $stream = ssh2_exec($connection, 'docker stats --no-stream --format "{{.NetIO}}" '.$param['docker_name_unit']);
                stream_set_blocking($stream, true);
                $docker_stats_output = stream_get_contents($stream);
                $net = explode(' ', $docker_stats_output)[0];
            }

            ssh2_exec($connection, 'docker '.$action.' '.$param['docker_name'].' '.$param['docker_name_unit']);

            ssh2_exec($connection, 'exit');
            unset($connection);

        } catch (\Exception $exception) {
            Log::error('Docker '.$action.' failed. error: '.$exception->getMessage());
        }

        return $net;
    }
}
