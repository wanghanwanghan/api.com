<?php

namespace App\Http\Controllers\zhanluewang_app\comment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Conference extends CommentBase
{
    public $categoryid=-7;

    public function getCommentlist(Request $request)
    {
        $this->prefixForRedis='Conference_';

        $infoid=$request->input('infoid');
        $userid=$request->input('userid');

        if ($infoid=='' || $userid=='')
        {
            return response()->json('input error');
        }

        if (!is_numeric($infoid) || !is_numeric($userid) || $userid==0)
        {
            return response()->json('input error');
        }

        //缓存中有就返回
        if ($res=$this->searchInRedis($request)) return response()->json($res);

        $sql="select t1.id,infoid,userid,username,content,avatar 
              from iiss_infocomment as t1 
              left join comment_user_avatar as t2 on t1.userid=t2.id 
              where infoid={$infoid} and categoryid={$this->categoryid} and userid={$userid} and ischeck=0";

        $sql=str_replace(["\r\n","\n"],'',$sql);

        $res=DB::connection('zhanluewang_app')->select($sql);

        $this->insertToRedis($request,$res);

        return response()->json($res);
    }
}
