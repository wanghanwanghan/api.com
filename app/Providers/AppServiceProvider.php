<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JPush\Client as JPushClient;

use App\Notifications\Channels\JPushChannel;
use Illuminate\Notifications\ChannelManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册 JPush 客户端单例
        //$this->app->singleton(JPushClient::class, function ($app) {
        //    $options = [
        //        $app->config->get('jpush.key'),
        //        $app->config->get('jpush.secret'),
        //        $app->config->get('jpush.log'),
        //    ];

        //    return new JPushClient(...$options);
        //});

        // 添加 JPush 驱动
        //$this->app->extend(ChannelManager::class, function ($manager) {
        //    $manager->extend('jpush', function ($app) {
        //        return $app->make(JPushChannel::class);
        //    });
        //});





    }
}
