<?php
/**
 * Custom helpers function
 */

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