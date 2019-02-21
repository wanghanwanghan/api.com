<?php

namespace App\Http\Controllers\zhanluewang_app;

use App\Http\Controllers\zhanluewang_app\comment\CommentBase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class zhanluewangBase extends Controller
{
    public function index(Request $request)
    {
        //取得请求地址，判断要执行的动作
        $requestUrl=$request->getRequestUri();

        $action=strrchr($requestUrl,'/');

        switch ($action)
        {
            case '/get_commentlist':

                return (new CommentBase())->chose($request);

                break;

            case 'wanghan':

                break;

            default:

                return response()->json('default');
        }









    }

}
