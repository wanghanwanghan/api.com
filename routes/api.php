<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request){
    return $request->user();
});

//战略军事评论相关
Route::group(['namespace'=>'zhanluewang_app'],function (){

    Route::match(['post'],'/get_commentlist', 'zhanluewangBase@index');





});

//内容审核
Route::group(['namespace'=>'Service\ContentCheck'],function (){

    Route::match(['post'],'/ContentCheck', 'ContentCheckBase@index');





});











