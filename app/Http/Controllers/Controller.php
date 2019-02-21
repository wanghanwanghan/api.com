<?php

namespace App\Http\Controllers;

use App\Http\Model\en_web\news;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use QL\QueryList;
use Ramsey\Uuid\Uuid;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //测试爬虫 抓取一个英文网
    public function QueryListForWeb()
    {
        //$ql=QueryList::get('http://www.ecns.cn/news/more.d.html?nid=121');

        //$title=$ql->find('.bdylft','.floatlft','.marr20')->find('h3>a')->texts();

        //$link=$ql->find('.bdylft','.floatlft','.marr20')->find('h3>a')->attrs('href');

        //$description=$ql->find('.bdylft','.floatlft','.marr20')->find('li>p')->texts();

        $rules=[
            'title'=>      ['.bdylft.floatlft.marr20 h3>a','text'],
            'link'=>       ['.bdylft.floatlft.marr20 h3>a','href'],
            'description'=>['.bdylft.floatlft.marr20 li>p','text']
        ];

        $data=QueryList::get('http://www.ecns.cn/news/more.d.html?nid=121')->rules($rules)->queryData();

        if (!is_array($data) || empty($data) || count($data)<20)
        {
            return ['QueryList Error'];
        }

        //是否可以正常抓取内容
        foreach ($data as &$row)
        {
            //$row是每一条新闻，通过link再继续采集详情
            $ql=QueryList::get($row['link']);

            //详情发布时间
            $time=current($ql->find('.downinfo.dottlne span:eq(1)')->texts()->all());
            $row['time']=$time;

            //详情内容
            $content=$ql->find('#yanse')->children('p')->texts()->all();
            $row['content']=array_filter($content);

            //详情中的图片
            $pic=$ql->find('#yanse img')->attrs('src')->all();
            $row['pic']=array_filter($pic,function ($OnePicUrl) {

                $tmpPic=null;

                if (preg_match('/image/',$OnePicUrl))
                {
                    $tmpPic[]=$OnePicUrl;
                }

                return $tmpPic;
            });
        }
        unset($row);

        //上一次更新数据库时间
        if (Redis::get('en_web_update_time')=='')
        {
            Redis::set('en_web_update_time','123');
            $last=123;
        }else
        {
            $last=Redis::get('en_web_update_time');
        }

        $news=[];
        //是否有需要更新到数据库的文章
        foreach ($data as $row)
        {
            if (strtotime($row['time']) < $last)
            {
                continue;
            }

            $news[]=$row;
        }

        if (empty($news))
        {
            return ['NO NEWS'];
        }else
        {
            $updateNewsCount=count($news);
        }

        //新建表
        if(!Schema::connection('en_web')->hasTable('news'))
        {
            Schema::connection('en_web')->create('news',function (Blueprint $table)
            {
                $table->increments('id')->unsigned()->comment('自增');
                $table->string('uuid');
                $table->string('title');
                $table->string('link')->comment('原文链接');
                $table->text('description');
                $table->string('time');
                $table->text('content');
                $table->text('pic');
                $table->integer('insert_time')->unsigned();
                $table->engine='innodb';
            });
        }

        //处理要插入的数据
        foreach ($news as &$row)
        {
            $row['title']="'".addslashes(strip_tags(trim($row['title'])))."'";

            $row['link']="'".addslashes(strip_tags(trim($row['link'])))."'";

            $row['description']="'".addslashes(strip_tags(trim($row['description'])))."'";

            $row['time']="'".addslashes(strip_tags(trim($row['time'])))."'";

            $tmp_content="'";
            //内容
            foreach ($row['content'] as $one_content)
            {
                $tmp_content.='<p>'.addslashes(strip_tags(trim($one_content)));
            }

            $tmp_content=$tmp_content."'";

            $row['content']=$tmp_content;

            //图片
            if (empty($row['pic']))
            {
                $row['pic']='';
            }else
            {
                $tmp_pic="'";
                foreach ($row['pic'] as $one_pic)
                {
                    $tmp_pic.='<p>'.$one_pic;
                }

                $row['pic']=$tmp_pic."'";
            }

            $time=time();

            news::create([
                'title'=>$row['title'],
                'uuid'=>Uuid::uuid1()->getHex(),
                'link'=>$row['link'],
                'description'=>$row['description'],
                'time'=>$row['time'],
                'content'=>$row['content'],
                'pic'=>$row['pic'],
                'insert_time'=>$time
            ]);

            sleep(1);
        }
        unset($row);

        Redis::set('en_web_update_time',time());

        Log::info('en_web更新完成：'.date('Y-m-d H:i:s',time()).'  更新文章数：'.$updateNewsCount);

        return ['Update Success'];
    }

    //生成静态页面
    public function test($uuid)
    {
        $data=news::where('uuid',$uuid)->first()->toArray();

        //标题转换
        $title=str_replace("\'","'",trim($data['title'],"'"));
        $title=str_replace('\"','"',$title);
        $data['title']=$title;

        //内容转换
        $content=str_replace("\'","'",trim($data['content'],"'"));
        $content=str_replace('\"','"',$content);
        $content=explode('<p>',$content);
        $content=array_filter($content);
        $data['content']=$content;

        //图片转换
        $pic=str_replace("\'","'",trim($data['pic'],"'"));
        $pic=explode('<p>',$pic);
        $data['pic']=array_filter($pic);

        //最近新闻
        $related=news::where('pic','!=','')->orderBy('insert_time','desc')->limit(100)->get(['uuid','pic','title'])->toArray();

        $tmp=[];
        foreach ($related as $row)
        {
            $row['pic']=trim($row['pic'],"'");

            $row['pic']=current(array_filter(explode('<p>',$row['pic'])));

            if (!empty($row['pic']) && $row['pic']!='')
            {
                $title=str_replace("\'","'",trim($row['title'],"'"));
                $title=str_replace('\"','"',$title);
                $title=substr($title,0,57).'...';

                $tmp[]=['uuid'=>$row['uuid'],'pic'=>$row['pic'],'title'=>$title];
            }

            if (!empty($tmp) && count($tmp)>=10)
            {
                break;
            }
        }

        $related=$tmp;

        //laravel做静态页的方法
        //第一，先拿到渲染后的页面
        //$v=view('en_web.detail',['data'=>$data,'related'=>$related]);
        //第二，把页面存起来
        //file_put_contents(public_path().'/new.html',response($v)->getContent());

        return view('en_web.detail',['data'=>$data,'related'=>$related]);
    }

    //战略军事评论测试
    public function comment()
    {
        $PDO=new \PDO('mysql:host=183.136.232.214;dbname=comment','chinabody','chinaiiss(!@#)');

        $PDO->query('SET NAMES utf8');

        $sql="select t1.id,infoid,userid,username,content,avatar 
              from iiss_infocomment as t1 
              left join comment_user_avatar as t2 on t1.userid=t2.id 
              where infoid=134280 and categoryid=13 and userid=1 and ischeck=1";

        $sql=str_replace(["\r\n","\n"],'',$sql);


        $res=$PDO->query($sql);
        $res->setFetchMode(\PDO::FETCH_ASSOC);
        $final=null;
        while ($row=$res->fetch())
        {
            $final[]=$row;
        }


        dd($final);



    }

    //玩玩jpush
    public function test_jpush()
    {






    }
}
