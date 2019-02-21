<?php

namespace App\Http\Controllers\Service\ContentCheck;

use App\Http\Controllers\Service\ServiceBase;
use App\Http\Helper\CURL;
use Illuminate\Http\Request;

class ContentCheckBase extends ServiceBase
{
    //git小猫图片
    //https://github.githubassets.com/images/spinners/octocat-spinner-32.gif

    //命令行查天气
    //curl wttr.in/beijing\?lang=zh

    public $label=[
        0=>'绝对没有',
        1=>'暴恐违禁',
        2=>'文本色情',
        3=>'政治敏感',
        4=>'恶意推广',
        5=>'低俗辱骂',
        6=>'低质灌水'
    ];

    public function index(Request $request)
    {
        $content=$request->input('content');

        if ($content=='' || empty($content)) return response()->json('content is null');
        if (strlen($content)>=1500) return response()->json('content must less than 1500');

        $url='https://aip.baidubce.com/rest/2.0/antispam/v2/spam?access_token=24.7795a51d4b2258332295eb4ec72acd7c.2592000.1551498284.282335-15517203';

        $content=['content'=>$content];

        $res=CURL::send($url,$content,true,['Content-Type:application/x-www-form-urlencoded']);

        $res=json_decode($res['msg']);

        $logid=$res->log_id;

        $result=$res->result;

        $res=null;

        //处理结果
        if (!empty($result->review) || !empty($result->reject))
        {
            //含有违禁
            foreach ($result->review as $row)
            {
                if (array_key_exists($row->label,$this->label))
                {
                    $label=$this->label[$row->label];
                }

                $res[]=['label'=>$label,'hit'=>$row->hit];
            }

            foreach ($result->reject as $row)
            {
                if (array_key_exists($row->label,$this->label))
                {
                    $label=$this->label[$row->label];
                }

                $res[]=['label'=>$label,'hit'=>$row->hit];
            }
        }

        return response()->json($res);
    }
}