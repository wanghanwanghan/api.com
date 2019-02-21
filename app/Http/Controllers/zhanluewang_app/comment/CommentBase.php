<?php

namespace App\Http\Controllers\zhanluewang_app\comment;

use App\Http\Controllers\zhanluewang_app\zhanluewangBase;
use App\Http\Helper\MyRedis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CommentBase extends zhanluewangBase
{
    public $prefixForRedis;

    public function searchInRedis(Request $request)
    {
        if (is_array($request->all()) && !empty($request->all()))
        {
            $key=implode(',',$request->all());

        }else
        {
            return false;
        }

        $key=$this->prefixForRedis.md5($key);

        if ($res=Redis::get($key))
        {
            return json_decode($res);

        }else
        {
            return false;
        }
    }

    public function insertToRedis(Request $request,$res,$ex=10)
    {
        if (is_array($request->all()) && !empty($request->all()))
        {
            $key=implode(',',$request->all());

        }else
        {
            return false;
        }

        $key=$this->prefixForRedis.md5($key);

        MyRedis::redis_set($key,json_encode($res),$ex);

        return true;
    }

    public function chose(Request $request)
    {
        switch (strtolower($request->input('column')))
        {
            case 'conference':

                return (new Conference())->getCommentlist($request);

                break;

            case 'news':

                return (new News())->getCommentlist($request);

                break;

            case 'pk':

                return (new PK())->getCommentlist($request);

                break;

            default:

                return response()->json('input error');
        }
    }
}
