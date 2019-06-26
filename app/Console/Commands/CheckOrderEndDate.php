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
            $username = 'root';
            $password = $server->ssh_pwd;
            $ip = $server->ip;
            $port = $server->ssh_port;
            $docker_name = $order->docker_name;
            $docker_name_unit = substr($order->docker_name, 0, -2).(substr($order->docker_name, -2)+0);

            try {
                $connection = ssh2_connect($ip, $port);
                ssh2_auth_password($connection, $username, $password);

                ssh2_exec($connection, 'docker stop '.$docker_name.' '.$docker_name_unit);

                ssh2_exec($connection, 'exit');
                unset($connection);

            } catch (\Exception $exception) {
                Log::error('Docker stop failed. error: '.$exception->getMessage());
                continue;
            }

            // 訂單設為過期狀態
            $order->update(['status' => Order::STATUS_EXPIRED]);

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
            foreach (config('wechatpush.send_key') as $send_key) {
                $text = $order->customer->name.'_使用日到期，Docker已暫停';
                $desp = sprintf('Customer: %s'.PHP_EOL.PHP_EOL.
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
                try {
                    $url = sprintf(
                        'https://sc.ftqq.com/%s.send?text=%s&desp=%s',
                        urlencode($send_key),
                        urlencode($text),
                        urlencode($desp)
                    );
                    file_get_contents($url);
                } catch (\Exception $e) {
                    Log::error('Wechat push error:'.$e->getMessage());
                }
            }
        }
    }
}
