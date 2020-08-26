<?php
/**
 * Custom helpers function
 */

use App\Models\VPS\Server;
use Illuminate\Support\Facades\Log;

/**
 * 微信推送
 * @param $text
 * @param $desp
 * @return void
 * @author Jeff Lin
 */
if (! function_exists('wechatPush')) {
    function wechatPush($text = 'title', $desp = 'description')
    {
        foreach (config('wechatpush.send_key') as $send_key) {
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

/**
 * 取得 Docker Name List
 * @param int $count
 * @return array
 * @author Jeff Lin
 */
if (! function_exists('getDockerNameList')) {
    function getDockerNameList($count = 10)
    {
        $list = [];
        for ($i = 1; $i <= $count; $i++) {
            $docker_name = 'v2ray-'.str_pad($i, 2, "0", STR_PAD_LEFT);
            $list[$docker_name] = $docker_name;
        }

        return $list;
    }
}

/**
 * 取得 Provider Docker Count
 * @param $provider
 * @return mixed
 * @author Jeff Lin
 */
if (! function_exists('getProviderDockerCount')) {

    function getProviderDockerCount($provider)
    {
        $provider_docker_count = [
            Server::PROVIDER_GOOGLE => 10,
            Server::PROVIDER_BANDWAGON => 10,
            Server::PROVIDER_HOSTWINDS => 15,
            Server::PROVIDER_LINODE => 15,
            Server::PROVIDER_DIGITALOCEAN => 15,
        ];

        return $provider_docker_count[$provider];
    }
}