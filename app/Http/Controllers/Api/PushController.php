<?php

namespace App\Http\Controllers\Api;

use App\Models\Order\Order;
use App\Models\VPS\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PushController extends Controller
{
    /**
     * 流量超出限制，推送消息
     *
     * @param Request $request
     * @return void
     * @author Jeff Lin
     */
    public function netLimit(Request $request)
    {
        $docker_name = 'v2ray-'.(str_pad(substr($request->docker_name, 6), 2, '0', STR_PAD_LEFT));
        $server = Server::where('ip', $request->ip)->first();
        if ($server) {
            $order = Order::where('server_id', $server->id)
                ->where('docker_name', $docker_name)
                ->where('status', Order::STATUS_ENABLE)
                ->orderBy('end_date', 'desc')
                ->first();
        } else {
            $order = null;
        }

        // Wechat消息推送
        $customer = $order ? $order->customer->name : '未知用戶';
        $text = $customer.'_流量使用超過限制，Docker已暫停';
        $desp = sprintf(
            'Customer: %s'.PHP_EOL.PHP_EOL.
            'IP: %s'.PHP_EOL.PHP_EOL.
            'Docker name: %s'.PHP_EOL.PHP_EOL.
            'Net: %s'.PHP_EOL.PHP_EOL.
            'Action: %s',
            $customer,
            $request->ip,
            $docker_name,
            $request->net,
            'Docker 停止'
        );
        wechatPush($text, $desp);
    }
}
