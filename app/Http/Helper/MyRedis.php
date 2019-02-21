<?php

namespace App\Http\Helper;

use Illuminate\Support\Facades\Redis;

class MyRedis
{
    public static function redis_set($key,$value,$time='')
    {
        if ($time!='')
        {
            Redis::set($key,$value);
            Redis::expire($key,$time);
        }else
        {
            Redis::set($key,$value);
        }
    }
}