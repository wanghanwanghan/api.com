<?php



Route::post('/data/ajax',function (\Illuminate\Http\Request $request){

    $pd=new Parsedown();

    //传过来的md转成html
    $html=str_replace(["\n","\r\n"],'',$pd->text($request->key));

    return ['code'=>"0",'data'=>$html];

});

Route::get('/comment','Controller@comment');


//Route::middleware('page-cache')->get('/test/{uuid}','Controller@test');

Route::get('/test/{uuid}','Controller@test');



Route::get('/en_web','Controller@QueryListForWeb');

//兜底路由
Route::fallback(function (){return 'no page';});

//临时统计我的路app的创建和分享次数
Route::get('/tongji/{str}', function ($str) {

    if ($str=='chuangjian')
    {
        \Illuminate\Support\Facades\Redis::INCR('chuangjian');
    }

    if ($str=='dingzhijihua')
    {
        \Illuminate\Support\Facades\Redis::INCR('dingzhijihua');
    }

    if ($str=='fenxiangjihua')
    {
        \Illuminate\Support\Facades\Redis::INCR('fenxiangjihua');
    }

    return 1;

});






