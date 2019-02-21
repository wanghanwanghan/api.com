<?php

namespace App\Notifications\Channels;

use JPush\Client as JPushClient;
use Illuminate\Notifications\Notification;

class JPushChannel
{
    protected $client;

    /**
     * Create the notification channel instance.
     *
     * @param \JPush\Client $client
     */
    public function __construct(JPushClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public function send($notifiable, Notification $notification)
    {
        $push = $notification->toJPush($notifiable, $this->client->push());

        // 这里是为了屏蔽极光服务器没有找到设备等情况报错，
        try
        {
            $push->send();

        }catch(\Throwable $th)
        {
            //throw $th;
        }
    }
}