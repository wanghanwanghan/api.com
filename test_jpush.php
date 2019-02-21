<?php

namespace data\tools;

use data\tools\config\Output;
use JPush\Client as Client;

class tsetpjusl
{
    const PUSH_TYPE = [
        'push_new_info' => '1',
        'push_visitor_alert' => '2'

    ];

    const APP_NAME = "****";

    public static function pushNewInfoNotice ($uids, $title, $url, $txt, $type = '1')
    {

        $ext = [
            'push_type' => strval (self::PUSH_TYPE[ 'push_new_info' ]),
            'info_type' => strval ($type),//1-资讯,2-项目
            'title' => empty($title) ? self::APP_NAME : $title,
            'content' => $txt,
            'redirect_url' => $url
        ];

        $res = JPush::pushMessageByAlias ($title, $txt, $uids, $ext);
        return $res;
    }
}
class JPush
{
    /**
     * 通过别名发送极光推送消息
     * @param $title // 标题
     * @param $content // 内容
     * @param $alias // 别名
     * @param array $params // 扩展字段
     * @param string $ios_badge // ios 角标数
     * @param array $platform // 推送设备
     * @return array|bool
     * @author huangzhicheng 2018年08月29日
     */
    public static function pushMessageByAlias ($title, $content, $alias, $params = [], $ios_badge = '0', $platform = ['ios', 'android'])
    {

        if (!is_array ($alias)) return false;
        $jpush_conf = Output::getJPushKey (); // 获取配置信息 app_key 和 master_secret

        $app_key = $jpush_conf[ 'app_key' ];
        $master_secret = $jpush_conf[ 'master_secret' ];
        try {
            // 初始化
            $client = new Client($app_key, $master_secret);

            $result = $client->push ()
                ->setPlatform ($platform)
                ->addAlias ($alias)
                ->iosNotification (
                    $content, [
                    'sound' => '1', // 是否有声音
                    'badge' => (int)$ios_badge, // 显示的角标数
                    'content-available' => true, // 去文档中查看具体用处,一般设置为true或者1
                    'category' => 'jiguang', // 这里也去文档中查看吧
                    'extras' => $params, // 扩展字段 根据自己业务场景来定.
                ])
                ->androidNotification ($content, [
                    'title' => $title,
                    //'build_id' => 2,
                    'extras' => $params,
                ])
                ->options ([
                    'sendno' => 100,
                    'time_to_live' => 86400,
                    'apns_production' => true, // ios推送证书的选择，True 表示推送生产环境，False 表示要推送开发环境
                    //'big_push_duration' => 10,
                ])
                ->send ();
            return $result;
        } catch (\Exception $e) {
            // 写入错误日志
            // 这里根据自己的业务来定
        }
    }
}