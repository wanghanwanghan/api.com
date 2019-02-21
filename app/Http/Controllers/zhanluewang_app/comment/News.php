<?php

namespace App\Http\Controllers\zhanluewang_app\comment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class News extends CommentBase
{
    public function getCommentlist(Request $request)
    {
        $this->prefixForRedis='News_';

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

        //$categoryid从mobile库中取
        $res=DB::connection('zhanluewang_app_mobile')->table('iiss_mobile_article')->where(['id'=>$infoid])->first();

        if (empty($res)) return response()->json('input error');

        $categoryid=!empty($res->foreigncategoryid)?$res->foreigncategoryid:$res->categoryid;

        $infoid=!empty($res->artid)?$res->artid:$infoid;

        $sql="select t1.id,infoid,userid,username,content,avatar 
              from iiss_infocomment as t1 
              left join comment_user_avatar as t2 on t1.userid=t2.id 
              where infoid={$infoid} and categoryid={$categoryid} and userid={$userid} and ischeck=0";

        $sql=str_replace(["\r\n","\n"],'',$sql);

        $res=DB::connection('zhanluewang_app')->select($sql);

        $this->insertToRedis($request,$res);

        return response()->json($res);
    }
}
