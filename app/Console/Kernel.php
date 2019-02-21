<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Redis;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function (){
            $key=str_random();
            Redis::set($key,str_random());
            Redis::expire($key,30);
        })->everyMinute();

        $schedule->call(function (){
            $fp=@fopen("/root/work/crontab_test","a+");
            fwrite($fp,str_random());
            fclose($fp);
        })->everyMinute();

        $schedule->command('crontest:test')->everyMinute();

        //爬取联通手机靓号
        $schedule->command('get:lianghao')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
