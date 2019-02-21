<?php

namespace App\Http\Controllers\zhanluewang_app\comment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PK extends CommentBase
{
    public $categoryid=-1;

    public function getCommentlist(Request $request)
    {
        $this->prefixForRedis='PK_';

        $infoid=$request->input('infoid');
        $userid=$request->input('userid');

        //1是正方，2是反方
        $vote=$request->input('vote');

        if ($infoid=='' || $userid=='' || $vote=='')
        {
            return response()->json('input error');
        }

        if (!is_numeric($infoid) || !is_numeric($userid) || $userid==0 || ($vote!=1 && $vote!=2))
        {
            return response()->json('input error');
        }

        if ($vote==1)
        {
            $vote=1;
        }else
        {
            $vote=0;
        }

        //缓存中有就返回
        if ($res=$this->searchInRedis($request)) return response()->json($res);

        //$categoryid从mobile库中取
        $res=DB::connection('zhanluewang_app_mobile')->table('iiss_mobile_pk')->where(['pkid'=>$infoid])->first();

        if (empty($res)) return response()->json('input error');

        $sql="select t1.id,infoid,userid,username,content,avatar 
              from iiss_infocomment as t1 
              left join comment_user_avatar as t2 on t1.userid=t2.id 
              where infoid={$infoid} and categoryid={$this->categoryid} and userid={$userid} and type={$vote} and ischeck=0";

        $sql=str_replace(["\r\n","\n"],'',$sql);

        $res=DB::connection('zhanluewang_app')->select($sql);

        $this->insertToRedis($request,$res);

        return response()->json($res);
    }
}
