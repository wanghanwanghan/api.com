<?php

namespace App\Http\Helper;

class CURL
{
    public static function send($url,$data,$isPost=true,$headerArray=[]):array
    {
        $curl=curl_init();//初始化

        curl_setopt($curl,CURLOPT_URL,$url);//设置请求地址

        curl_setopt($curl,CURLOPT_POST,$isPost);//设置post方式请求

        if (!empty($headerArray) && is_array($headerArray)) curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);

        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);//几秒后没链接上就自动断开

        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);

        //$data=json_encode($data);//转换成json

        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//提交的数据

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);//返回值不直接显示

        $res=curl_exec($curl);//发送请求

        if(curl_errno($curl))//判断是否有错
        {
            $msg=null;
            $msg=curl_error($curl);
            curl_close($curl);//释放

            return ['error'=>'1','msg'=>$msg];
        }else
        {
            curl_close($curl);//释放
            return ['error'=>'0','msg'=>$res];
        }
    }
}