<?php
/**
 * Created by PhpStorm.
 * User: wanghan
 * Date: 2019/2/15
 * Time: 13:20
 */
class updateAgentInfo
{
    //导入还是删除
    //import delete
    public $action;
    //期号
    public $issueNo;
    //0全部，1初审，2注册
    public $type;
    //代理机构名称
    public $AgName;

    //导入状态，默认是false，如果sql出问题，设置成true
    public $ImpStatus=false;

    public function __construct()
    {
        set_time_limit(0);
        date_default_timezone_set('PRC');
    }

    //检查传入参数
    public function checkArgs($args)
    {
        //新加入$AgName，如果$AgName不是空，说明要按照代理机构删除，或者导入
        if ($args['AgName']!='')
        {
            if (isset($args['AgName']) && is_string($args['AgName']) && strlen($args['AgName'])>1)
            {
                $this->AgName=$args['AgName'];
            }else
            {
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                return false;
            }
        }

        //action是导入的时候，issue可以是空，空说明是导入最新期，不是空导入指定期
        if (isset($args['action']) && isset($args['issueNo']))
        {
            if ($args['action']=='import' || $args['action']=='delete')
            {
                if ($args['action']=='import')
                {
                    //导入时候，issue可以是空
                    if ($args['issueNo']=='' || is_numeric($args['issueNo']))
                    {
                        $args['type']=$args['type']-0;

                        if ($args['type']===0 || $args['type']===1 || $args['type']===2)
                        {
                            $this->action=$args['action'];
                            $this->issueNo=$args['issueNo'];
                            $this->type=$args['type'];
                            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>'传入参数正确','time'=>date('Y-m-d_H:i:s',time())]);
                            return true;
                        }else
                        {
                            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                            return false;
                        }
                    }else
                    {
                        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                        return false;
                    }
                }

                if ($args['action']=='delete')
                {
                    //删除时候，issue必须有值
                    if ($args['issueNo']!='' && is_numeric($args['issueNo']))
                    {
                        $args['type']=$args['type']-0;

                        if ($args['type']===0 || $args['type']===1 || $args['type']===2)
                        {
                            $this->action=$args['action'];
                            $this->issueNo=$args['issueNo'];
                            $this->type=$args['type'];
                            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>'传入参数正确','time'=>date('Y-m-d_H:i:s',time())]);
                            return true;
                        }else
                        {
                            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                            return false;
                        }
                    }else
                    {
                        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                        return false;
                    }
                }

            }else
            {
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
                return false;
            }

        }else
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }
    }

    //检查当前期号的数据状态
    public function checkDataStatus()
    {
        //$issueNo是空就获取最大期号
        //不是空就直接用
        //但是生产环境第一次运行导入数据时候，应该是从最大期号向最小期号（1592）导
        if ($this->issueNo==null || empty($this->issueNo) || !is_numeric($this->issueNo))
        {
            $this->issueNo=$this->getIssueNo();
        }

        //导入action可以操作的数据状态是：数据库中查询不到当前期号的数据，无论是导入中、导入成功还是导入失败
        if ($this->action=='import')
        {
            //取得数据库对象
            $link=ConnectMysql::getSingleton()->ConnWithPDO();

            if ($link===false) return false;

            //查询数据库中$this->issueNo期的初审或者注册有没有导入过
            $sql="select IssueNo,AnnType,ImpStatus from RECORD_IMPORT_MARK where IssueNo=$this->issueNo ";

            if ($this->type===0) $sql.='and (AnnType=1 or AnnType=2) limit 1';
            if ($this->type===1) $sql.='and AnnType=1 limit 1';
            if ($this->type===2) $sql.='and AnnType=2 limit 1';

            $res=$link->query($sql);
            $res->setFetchMode(\PDO::FETCH_ASSOC);
            $final=null;
            while ($row=$res->fetch())
            {
                $final[]=$row;
            }

            if ($this->type===0)
            {
                $ann='全部';
            }else
            {
                $ann=$this->type===1 ? '初审' : '注册';
            }

            //是null说明没有查询到记录
            if ($final==null)
            {
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo."期，公告类型：{$ann}可以导入数据",'time'=>date('Y-m-d_H:i:s',time())]);
                return true;
            }else
            {
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo."期，公告类型：{$ann}，已存在，不可导入",'time'=>date('Y-m-d_H:i:s',time())]);
                return false;
            }
        }

        //删除action可以操作的数据状态是：导入成功和导入失败。导入中不可以操作，因为删不干净，除非kill进程
        if ($this->action=='delete')
        {
            //必须有issueNo
            if ($this->issueNo==null || empty($this->issueNo) || !is_numeric($this->issueNo)) return false;

            if ($this->type!==0 && $this->type!==1 && $this->type!==2) return false;

            //取得数据库对象
            $link=ConnectMysql::getSingleton()->ConnWithPDO();

            if ($link===false) return false;

            //查询数据库中$this->issueNo的状态，只要不是正在导入，就可以删除
            if ($this->type===0)
            {
                //初审注册都要删
                //查询数据库中$this->issueNo期，如果有ImpStatus=1也就是正在导入状态，就不能删，不kill进程删不干净
                $sql="select IssueNo,AnnType,ImpStatus from RECORD_IMPORT_MARK where IssueNo=$this->issueNo and ImpStatus=1";
                $res=$link->query($sql);
                $res->setFetchMode(\PDO::FETCH_ASSOC);
                $final=null;
                while ($row=$res->fetch())
                {
                    $final[]=$row;
                }

                //是null说明没有查询到记录
                if ($final==null)
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo.'期的初审和注册可以删除','time'=>date('Y-m-d_H:i:s',time())]);
                    return true;
                }else
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo.'期的初审和注册不能删除，有正在导入状态','time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }
            }elseif ($this->type===1 || $this->type===2)
            {
                //删除公告类型（初审）（注册）
                //状态表中的当前期的公告类型只要不是正在导入状态，就可以删除
                $sql="select IssueNo,AnnType,ImpStatus from RECORD_IMPORT_MARK where IssueNo=$this->issueNo and AnnType=$this->type and ImpStatus=1 limit 1";
                $res=$link->query($sql);
                $res->setFetchMode(\PDO::FETCH_ASSOC);
                $final=null;
                while ($row=$res->fetch())
                {
                    $final[]=$row;
                }

                if ($this->type===0)
                {
                    $ann='全部';
                }else
                {
                    $ann=$this->type===1 ? '初审' : '注册';
                }

                //是null说明没有查询到记录
                if ($final==null)
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo."期的公告类型：{$ann}，可以删除",'time'=>date('Y-m-d_H:i:s',time())]);
                    return true;
                }else
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo."期的公告类型：{$ann}，不可以删除，状态是正在导入",'time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }
            }else
            {
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'删除动作的参数传递错误','time'=>date('Y-m-d_H:i:s',time())]);
                return false;
            }
        }
    }

    //获取期号，给$this->issueNo赋值
    public function getIssueNo()
    {
        //生产环境第一次运行导入数据特殊，需要从数据库中获取最小期号，如果是1592，说明已经都导入了，通过数据部接口获取最大期号就行
        //如果最小期号比1592大，就把期号减1，赋值给$this->issueNo
        //如果数据库中的期号是空，也从数据库获取最大期号

        $link=ConnectMysql::getSingleton()->ConnWithPDO();

        $sql='select IssueNo from RECORD_IMPORT_MARK order by IssueNo asc limit 1';
        $res=$link->query($sql);
        $res->setFetchMode(\PDO::FETCH_ASSOC);
        $final='';
        while ($row=$res->fetch())
        {
            $final=current($row);
        }

        //如果$final是空，说明数据表中是空，请求数据部接口
        if ($final=='') return SendRequest::getSingleton()->getMaxIssue() - 0;

        //如果不是空，如果期号比1592大，就减1后赋值，如果等于1592，就获取最大期号赋值
        if (is_numeric($final) && !empty($final) && $final > 1592)
        {
            return $final - 1;
        }else
        {
            return SendRequest::getSingleton()->getMaxIssue() - 0;
        }
    }

    //执行删除操作
    public function deleteByIssueNo()
    {
        //删除操作必须有issueNo

        //type是0，都删，是1，删初审，是2，删注册

        //先确保要删除的期，导入状态不是正在导入，不然删不干净

    }

    //检查数据库中是否有正在导入的任务
    public function checkSchedule()
    {
        //如果有正在导入的任务，就不执行导入

        //取得数据库对象
        $link=ConnectMysql::getSingleton()->ConnWithPDO();

        if ($link===false) return false;

        //查询有没有正在导入的任务
        $sql="select IssueNo,AnnType from RECORD_IMPORT_MARK where ImpStatus=1 limit 1";

        $res=$link->query($sql);
        $res->setFetchMode(\PDO::FETCH_ASSOC);
        $final=null;

        while ($row=$res->fetch())
        {
            $final[]=$row;
        }

        //是null说明没有查询到记录
        if ($final==null)
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>'没有查询到有正在执行的任务，准备开始导入','time'=>date('Y-m-d_H:i:s',time())]);
            return true;
        }else
        {
            $ann=$final[0]['AnnType']==1 ? '初审' : '注册';

            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$final[0]['IssueNo'].'期，'.'公告类型：'.$ann.'，正在导入中','time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }
    }

    //插入导入任务，在RECORD_IMPORT_MARK表中
    public function lockSchedule($cond)
    {
        if ($this->issueNo=='')
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，锁定任务时期号是空",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }
        if ($this->type!==0 && $this->type!==1 && $this->type!==2)
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，锁定任务时公告类型错误",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //不能同时进行多个导入任务
        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        $sql='insert into RECORD_IMPORT_MARK (`IssueNo`,`AnnType`,`AnnounceDate`,`ImpStatus`,`TimeStart`) VALUES ';
        $time=date('Y-m-d H:i:s',time());

        if ($this->type===1 || $this->type===2)
        {
            $type=$this->type;

            $sql.="($this->issueNo,$type,'".$cond['AnnounceDate']."',1,'".$time."')";

        }elseif ($this->type===0)
        {
            $sql.="($this->issueNo,1,'".$cond['AnnounceDate']."',1,'".$time."'),";
            $sql.="($this->issueNo,2,'".$cond['AnnounceDate']."',1,'".$time."')";

        }else
        {
        }

        if (!mysqli_query($link,$sql))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，锁定任务时写入数据库失败",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"锁定任务成功",'time'=>date('Y-m-d_H:i:s',time())]);
        return true;
    }

    //插入完成后，在RECORD_IMPORT_MARK表中解锁
    public function unlockSchedule($ggt)
    {
        if ($this->issueNo=='')
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，解锁任务时期号是空",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }
        if ($this->type!==0 && $this->type!==1 && $this->type!==2)
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，解锁任务时公告类型错误",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //解锁时候用$ggt，不用$this->type
        //unlock里要做的时候比较多
        //第一：更改在导入状态表里的状态，并且更新其他列数据
        //第二：计算初审或者注册表里的数据，插入goods表 --- 这项不在这里做了，拿到外面去，因为要导入每一个代理机构时候，拿到所有excel文件，方法名insertData2Goods
        //第三：给表加索引

        if ($ggt!==1 && $ggt!==2)
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，解锁任务时{$ggt}传入错误",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //3是导入中有错误的sql
        $ImpStatus=$this->ImpStatus===true ? 3 : 2;

        //1是初审
        $AnnType=$ggt===1 ? 'CHUSHEN' : 'ZHUCE';

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        //==============================================================================================================
        //首先先更改导入状态表里的数据=====================================================================================
        $time=date('Y-m-d H:i:s',time());
        $sql="update RECORD_IMPORT_MARK set ";
        $sql.="AgentAmount=(SELECT count(DISTINCT AgentId) from {$this->issueNo}_{$AnnType}_DETAIL), ";
        $sql.="ImpStatus={$ImpStatus}, ";
        $sql.="ImpAheadMarks=(SELECT count(*) from {$this->issueNo}_{$AnnType}_DETAIL where CheckType=1), ";
        $sql.="ImpSimilarMarks=(SELECT count(*) from {$this->issueNo}_{$AnnType}_DETAIL where CheckType=2), ";
        $sql.="TimeEnd='".$time."' ";
        $sql.="where IssueNo={$this->issueNo} and AnnType={$ggt};";

        $ann=$ggt===1 ? '初审' : '注册';

        if (!mysqli_query($link,$sql))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，{$this->issueNo}期，公告类型：{$ann}，解锁任务时写入数据库失败",'time'=>date('Y-m-d_H:i:s',time())]);
        }else
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}期，公告类型：{$ann}，解锁任务成功",'time'=>date('Y-m-d_H:i:s',time())]);
        }

        //==============================================================================================================
        //其次再计算GOODS表==============================================================================================
        //$sql="INSERT INTO {$this->issueNo}_GOODS_INFO(IssueNo,AnnType,AgentId,AheadMarks,SimilarMarks) ";
        //$sql.="SELECT {$this->issueNo},{$ggt},t1.AgentId,SUM(t1.AheadMarks) AS AheadMarks,SUM(t1.SimilarMarks) AS SimilarMarks ";
        //$sql.="FROM (";
        //$sql.="SELECT t0.AgentId,(CASE t0.CheckType WHEN 1 THEN t0.cc ELSE 0 END) AheadMarks,(CASE t0.CheckType WHEN 2 THEN t0.cc ELSE 0 END) SimilarMarks ";
        //$sql.="FROM (";
        //$sql.="SELECT AgentId,CheckType,1 AS AnnType,COUNT(*) AS cc FROM {$this->issueNo}_{$AnnType}_DETAILGROUP BY AgentId,CheckType";
        //$sql.=") t0";
        //$sql.=") t1 GROUP BY t1.AgentId;";

        //if (!mysqli_query($link,$sql))
        //{
        //    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，{$this->issueNo}期，公告类型{$ggt}，GOODS表写入数据库失败",'time'=>date('Y-m-d_H:i:s',time())]);
        //}else
        //{
        //    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}期，公告类型{$ggt}，GOODS表写入数据库成功",'time'=>date('Y-m-d_H:i:s',time())]);
        //}
        //==============================================================================================================
        //==============================================================================================================

        //==============================================================================================================
        //最后给初审或注册表加索引=========================================================================================
        $sql="alter table {$this->issueNo}_{$AnnType}_DETAIL add INDEX ind_AgentId_{$this->issueNo}_{$AnnType} (`AgentId`)";

        if (!mysqli_query($link,$sql))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，{$this->issueNo}_{$AnnType}_DETAIL表添加索引失败",'time'=>date('Y-m-d_H:i:s',time())]);
        }else
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}_{$AnnType}_DETAIL表添加索引成功",'time'=>date('Y-m-d_H:i:s',time())]);
        }
        //==============================================================================================================
        //==============================================================================================================
        return true;
    }

    //计算detail表，插入goods表
    public function insertData2Goods($ggt,$AgentId,$excel)
    {
        if (!is_array($excel) || empty($excel))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"居然是空！{$excel}",'time'=>date('Y-m-d_H:i:s',time())]);
            exit;
        }

        //拼接excel文件名，插入数据库时候用
        $excel_str='';

        foreach ($excel as $row)
        {
            $filename=current($row);

            $excel_str.=$filename.';';
        }

        $excel_str=rtrim($excel_str,';');

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        $AnnType=$ggt===1 ? 'CHUSHEN' : 'ZHUCE';
        $ann=$ggt===1 ? '初审' : '注册';

        $sql="INSERT INTO {$this->issueNo}_GOODS_INFO(AnnType,AgentId,AheadMarks,SimilarMarks,SourceFileUrl) ";
        $sql.="SELECT {$ggt} as AnnType,t1.AgentId,SUM(t1.AheadMarks) AS AheadMarks,SUM(t1.SimilarMarks) AS SimilarMarks,'".$excel_str."' as SourceFileUrl ";
        $sql.="FROM (";
        $sql.="SELECT t0.AgentId,(CASE t0.CheckType WHEN 1 THEN t0.cc ELSE 0 END) AheadMarks,(CASE t0.CheckType WHEN 2 THEN t0.cc ELSE 0 END) SimilarMarks ";
        $sql.="FROM (";
        $sql.="SELECT AgentId,CheckType,1 AS AnnType,COUNT(*) AS cc FROM {$this->issueNo}_{$AnnType}_DETAIL GROUP BY AgentId,CheckType";
        $sql.=") t0";
        $sql.=") t1 where t1.AgentId={$AgentId} GROUP BY t1.AgentId;";

        if (!mysqli_query($link,$sql))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，{$this->issueNo}期，公告类型：{$ann}，GOODS表写入数据库失败",'time'=>date('Y-m-d_H:i:s',time())]);
        }else
        {
            //Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}期，公告类型{$ggt}，GOODS表写入数据库成功",'time'=>date('Y-m-d_H:i:s',time())]);
        }
    }

    public function createTable()
    {
        $sql1="create table {$this->issueNo}_CHUSHEN_DETAIL like ZC_CHUSHEN_DETAIL";
        $sql2="create table {$this->issueNo}_ZHUCE_DETAIL like ZC_ZHUCE_DETAIL";
        $sql3="create table {$this->issueNo}_GOODS_INFO like ZC_GOODS_INFO";

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        mysqli_query($link,$sql1);
        mysqli_query($link,$sql2);
        mysqli_query($link,$sql3);

        return true;
    }

    //找出插入代理机构时错误的sql语句
    public function findErrSql($link,$sql)
    {
        if (!is_string($sql))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，执行findErrSql时，sql不是string",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //$sql是sql语句VALUES之前，$val是VALUES之后要插入的内容
        list($sql,$val)=explode('VALUES',$sql);

        //findErrSql方法用在了导入代理机构，和导入初审注册这两个地方
        //当导入代理机构时候出错时候，不能用"/(\(.{350,}\))+/U"这个匹配

        //正则匹配出每个需要插入的值
        //$pattern="/(\(.*\))+/U";
        $pattern="/(\(.{400,}\))+/U";
        preg_match_all($pattern,trim($val),$res);
        $res=$res[0];

        if (!is_array($res) || empty($res))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，执行findErrSql时，正则没有匹配到",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //接下来一个一个插入找出错误的写入log
        if ($link instanceof \PDO)
        {
            foreach ($res as $row)
            {
                $result=$link->query($sql.'VALUES '.$row);

                if (!$result)
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'sql错误：'.$sql.'VALUES '.$row,'time'=>date('Y-m-d_H:i:s',time())]);
                }
            }

            return true;

        }else
        {
            foreach ($res as $row)
            {
                $result=mysqli_query($link,$sql.'VALUES '.$row);

                if (!$result)
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'sql错误：'.$sql.'VALUES '.$row,'time'=>date('Y-m-d_H:i:s',time())]);
                }
            }

            return true;
        }
    }

    //单个代理机构导入
    public function importOneAgent()
    {
        //如果$this->AgName不是空，说明执行的是按照某一代理机构导入数据
        $AgentName=addslashes($this->AgName);

        //还要满足一个条件，现在$this->issueNo，$this->type都是有值的，要保证这期，这个公告类型被导入过
        if ($this->action!='import')
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'导入代理机构时，传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        if ($this->issueNo=='' || !is_numeric($this->issueNo))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'导入代理机构时，传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        if ($this->type!==1 && $this->type!==2)
        {
            //不提供type等于0的时候了，必须指定一个公告类型
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'导入代理机构时，传入参数错误','time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //查询这期，这个公告类型有没有被导入过
        $sql="select ImpStatus from RECORD_IMPORT_MARK where IssueNo=$this->issueNo and AnnType=$this->type limit 1";

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        $result=mysqli_query($link,$sql);

        if (!$result)
        {
            //查询失败
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，查询{$this->issueNo}期公告类型{$this->type}是否导入过时出错",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;

        }else
        {
            //查询成功
            if ($result->fetch_assoc()==null)
            {
                //没有数据等于没有导入过，不继续执行了
                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，{$this->issueNo}期公告类型{$this->type}还未导入过，不能单独执行一个代理机构的导入",'time'=>date('Y-m-d_H:i:s',time())]);
                return false;
            }
        }

        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}期，公告类型{$this->type}，代理机构名称{$AgentName}，准备开始导入",'time'=>date('Y-m-d_H:i:s',time())]);
        return true;
    }

    //单个代理机构数据导入
    public function importOneData()
    {
        //这里要做的是单个代理机构的数据导入
        //首先看代理机构存不存在，不存在就插入数据库
        $AgName=addslashes($this->AgName);

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        $sql="select AgentId from ZC_AGENT_INFO where AgentName='".$AgName."' limit 1";

        $result=mysqli_query($link,$sql);

        if (!$result)
        {
            //查询失败返回false
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"单个代理机构查询失败",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;

        }else
        {
            $res=$result->fetch_assoc();

            if ($res!=null)
            {
                //代理机构存在
                $AgentId=current($res)-0;

            }else
            {
                //代理机构不存在，插入后返回agentId
                $sql="insert into ZC_AGENT_INFO ('','".$AgName."')";

                $result=mysqli_query($link,$sql);

                if (!$result)
                {
                    //插入失败
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"插入单个代理机构失败",'time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }

                //获取刚刚插入的，代理机构的，id
                $sql="select AgentId from ZC_AGENT_INFO where AgentName='".$AgName."' limit 1";

                $result=mysqli_query($link,$sql);

                if (!$result)
                {
                    //查询失败
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"查询单个代理机构失败",'time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }else
                {
                    $res=$result->fetch_assoc();

                    if ($res!=null)
                    {
                        //代理机构存在
                        $AgentId=current($res)-0;

                    }else
                    {
                        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"刚插入的代理机构丢了",'time'=>date('Y-m-d_H:i:s',time())]);
                        return false;
                    }
                }
            }
        }

        //处理完了代理机构，下面要开始请求数据部接口拿excel了
        if ($AgentId=='') return false;

        $where['AgentName'] = $AgName;
        $where['AnnType'] = $this->type;//这里不是1就是2
        $where['issueno'] = $this->issueNo;

        //获取代理机构数据
        $info=SendRequest::getSingleton()->getAgentData($where);

        if (!isset($info[0]) || !is_array($info[0]))
        {
            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，插入单个代理机构数据是，数据部返回错误",'time'=>date('Y-m-d_H:i:s',time())]);
            return false;
        }

        //如果不是空数组就循环，是空数组不会进入循环
        foreach ($info[0] as $row)
        {
            $excelUrl_arr=current($row);

            if (!is_array($excelUrl_arr['excelUrl']))
            {
                //不是数组就进来，可能是空，或者是个string文件名
                if (isset($excelUrl_arr['excelUrl']) && !empty($excelUrl_arr['excelUrl']) && preg_match("/\.xls/",$excelUrl_arr['excelUrl'])>0)
                {
                    $excelUrl_arr['excelUrl']=[[$excelUrl_arr['excelUrl']]];
                }else
                {
                    //当前代理机构没有数据
                    $sql="insert into {$this->issueNo}_GOODS_INFO(AnnType,AgentId,AheadMarks,SimilarMarks) ";
                    $sql.="values ({$this->type},{$AgentId},0,0)";

                    $res=$link->query($sql);

                    if ($res==false)
                    {
                        $this->ImpStatus=true;
                        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，向GOODS表中插入空数据的代理机构出错，sql语句是：{$sql}",'time'=>date('Y-m-d_H:i:s',time())]);
                    }

                    continue;
                }
            }







        }

        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"单个代理机构数据导入完成",'time'=>date('Y-m-d_H:i:s',time())]);
        return true;
    }

    //执行导入数据之前,要导入当前期的代理机构
    public function importAgent()
    {
        //每期的机构会发生变化，目前知道的变化是：代理机构名称会加入（已注销）（待删除）这类文字
        //数据部的接口不是很稳定，要特别小心，以前循环5000次中途就会挂掉，解决办法是把循环次数降低，每次开始循环之前sleep
        $cond['issueno']=$this->issueNo;
        $cond['pageno']=1;
        $cond['pagesize']=100;

        //事前不知道总共有多少条数据，先请求一次，得到总共数据多少条，计算出有多少页，再循环
        //发送请求
        $res=SendRequest::getSingleton()->getAgentName($cond);

        //拿出来总记录数，算出要循环的页数
        $TotleData=$res[0]['csgg']['count'];
        //拿出公告日期
        $AnnounceDate['AnnounceDate']=$res[0]['csgg']['gonggaodate'];

        //要循环的总页数
        $loop=$TotleData/100+1;

        $link=ConnectMysql::getSingleton()->ConnWithMysqli();

        if ($link===false) return false;

        //锁住，禁止别的导入任务开始
        if (!$this->lockSchedule($AnnounceDate)) return false;

        //开始循环，取出一个代理机构名称，先在mysql中查询，如果有就不insert了，没有再insert
        for ($i=1;$i<$loop;$i++)
        {
            $cond['pageno']=$i;
            $res=SendRequest::getSingleton()->getAgentName($cond);

            //$res===false是接口没有正确返回，如果等待时间很长，也许是服务器挂了，或者solr挂了，再请求一次，如果还是这样就退出吧
            if ($res===false || $res===null)
            {
                $res=SendRequest::getSingleton()->getAgentName($cond);

                if ($res===false || $res===null)
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>'接口故障','time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }
            }

            //以上还不能保证确实有数据，也可能没有，还要确认一下$res[0]['csgg']['data']
            if (!isset($res[0]['csgg']['data']) || empty($res[0]['csgg']['data']))
            {
                //如果不含有，或者是空，就再请求一次，还不行就退出
                $res=SendRequest::getSingleton()->getAgentName($cond);

                if (!isset($res[0]['csgg']['data']) || empty($res[0]['csgg']['data']))
                {
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>$this->issueNo."期的代理机构导入出错，pagesize是{$cond['pagesize']}，页数是{$i}",'time'=>date('Y-m-d_H:i:s',time())]);
                    return false;
                }
            }

            //这下$res中应该有100个代理机构名称了
            $AgentName=$res[0]['csgg']['data'];

            foreach ($AgentName as $key=>$value)
            {
                $key=addslashes($key);
                $sql="select AgentName from ZC_AGENT_INFO where AgentName='".$key."' limit 1";

                $result=mysqli_query($link,$sql);

                if (!$result)
                {
                    //查询失败返回false，数据不存在返回null，有数据就返回数据
                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>$this->issueNo."期的查询是否含有代理机构错误，代理机构名是$key",'time'=>date('Y-m-d_H:i:s',time())]);
                    unset($AgentName[$key]);

                }else
                {
                    if ($result->fetch_assoc()!=null)
                    {
                        //有数据，unset掉，不插入
                        unset($AgentName[$key]);
                    }
                }
            }

            //unset没了，执行下一个100条
            if (!is_array($AgentName) || empty($AgentName)) continue;

            //如果还有需要插入
            $sql='';
            foreach ($AgentName as $key=>$value)
            {
                $key=addslashes($key);
                $sql.="(null,'".$key."'),";
            }
            $sql=rtrim($sql,',');
            $sql="insert into ZC_AGENT_INFO VALUES $sql";
            $result=mysqli_query($link,$sql);

            //这样插入是100个代理机构插入一次，如果有错误的，有可能是100分之1，要循环$sql中的所有代理机构，一条一条插入，找出错误的
            if (!$result)
            {
                //处理出错的sql，把出错的找出来
                $this->ImpStatus=true;

                //传入mysql资源和出错的sql
                $this->findErrSql($link,$sql);
            }

            sleep(1);
        }

        //能运行到这里说明代理机构已经插入完毕了，如果$this->ImpStatus=false，说明sql没有错的
        //如果$this->ImpStatus=true，说明代理机构有没插入的，检查log吧
        Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>$this->issueNo.'期代理机构导入完毕','time'=>date('Y-m-d_H:i:s',time())]);
        return true;
    }

    //根据代理机构，导入数据
    public function importData()
    {
        //这里要做的是根据数据库中的代理机构名称，从数据部取得excel表，解析后导入数据库

        //取得数据库对象
        $link=ConnectMysql::getSingleton()->ConnWithPDO();

        //查询一共有多少代理机构
        $sql="select count(*) from ZC_AGENT_INFO";
        $res=$link->query($sql);
        $res->setFetchMode(\PDO::FETCH_ASSOC);
        $final=null;
        while ($row=$res->fetch())
        {
            $final=current($row);
        }

        //计算多少页
        $loop2=$final/100+1;

        //导入初审还是注册，type等于0循环两次
        if ($this->type===0)
        {
            $loop1=2;
            $ggt=1;
        }
        if ($this->type===1 || $this->type===2)
        {
            $loop1=1;
            $ggt=$this->type;
        }

        for ($i=1;$i<=$loop1;$i++)
        {
            //循环代理机构表
            for ($j=1;$j<=$loop2;$j++)
            {
                $offset=($j - 1) * 100;
                $sql="select AgentId,AgentName from ZC_AGENT_INFO limit 100 offset $offset";
                $res=$link->query($sql);
                $res->setFetchMode(\PDO::FETCH_ASSOC);
                $final=null;
                while ($row=$res->fetch())
                {
                    $final[]=$row;
                }

                if ($final==null) break;

                //查询条件
                foreach ($final as $key => $value)
                {
                    $AgentId=$value['AgentId']-0;

                    $where['AgentName'] = $value['AgentName'];
                    $where['AnnType'] = $ggt;
                    $where['issueno'] = $this->issueNo;

                    $info=SendRequest::getSingleton()->getAgentData($where);

                    if (!isset($info[0]) || !is_array($info[0])) continue;

                    foreach ($info[0] as $row)
                    {
                        $excelUrl_arr=current($row);

                        if (!is_array($excelUrl_arr['excelUrl']))
                        {
                            //不是数组就进来，可能是空，或者是个string文件名
                            if (isset($excelUrl_arr['excelUrl']) && !empty($excelUrl_arr['excelUrl']) && preg_match("/\.xls/",$excelUrl_arr['excelUrl'])>0)
                            {
                                $excelUrl_arr['excelUrl']=[[$excelUrl_arr['excelUrl']]];
                            }else
                            {
                                //当前代理机构没有数据
                                $sql="insert into {$this->issueNo}_GOODS_INFO(AnnType,AgentId,AheadMarks,SimilarMarks) ";
                                $sql.="values ({$ggt},{$AgentId},0,0)";

                                $res=$link->query($sql);

                                if ($res==false)
                                {
                                    $this->ImpStatus=true;
                                    Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，向GOODS表中插入空数据的代理机构出错，sql语句是：{$sql}",'time'=>date('Y-m-d_H:i:s',time())]);
                                }

                                continue;
                            }
                        }

                        $CheckNo_prefix=null;
                        $CheckNo_suffix=null;
                        foreach ($excelUrl_arr['excelUrl'] as $roww)
                        {
                            //每一个excel
                            $excel=current($roww);
                            $res=ParseExcel::getSingleton()->readerExcelFile($this->issueNo,$excel);
                            if ($res=='CanNotReadExcel')
                            {
                                Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'-1','msg'=>"出错，excel文件打不开：$excel",'time'=>date('Y-m-d_H:i:s',time())]);
                                continue;
                            }
                            $cond=null;
                            $condAll=null;
                            foreach ($res as $k=>$v)
                            {
                                if ($k===0||$k===1) continue;
                                $cond=null;
                                //$v是要插入的数据
                                $patterns = "/在先商标/";
                                if (preg_match($patterns,$v[0])>0)
                                {
                                    $CheckType=1;
                                    $CheckNo_suffix=null;
                                    if ($CheckNo_prefix==null)
                                    {
                                        $CheckNo_prefix=1;
                                    }else
                                    {
                                        $CheckNo_prefix++;
                                    }
                                    $CheckNo=$SimilarNo=$CheckNo_prefix;
                                }else
                                {
                                    $CheckType=2;
                                    if ($CheckNo_suffix==null)
                                    {
                                        $CheckNo_suffix=1;
                                    }else
                                    {
                                        $CheckNo_suffix++;
                                    }
                                    $CheckNo=$CheckNo_prefix;
                                    $SimilarNo=$CheckNo_prefix.'_'.$CheckNo_suffix;
                                }

                                if ($v[8]=='') $v[8]="null";
                                if ($v[10]=='') $v[10]="null";
                                //$v[2]和$v[5]里可能含有单引号，把一个单引号变成两个
                                $v[2]=preg_replace('/\'/', "''", $v[2]);
                                $v[5]=preg_replace('/\'/', "''", $v[5]);

                                $cond.="($AgentId,$CheckNo,'".$SimilarNo."',$CheckType,'".$v[2]."','".$v[1]."',$v[4],'".$v[5]."',";
                                //$v里的6，7，9是时间，不存在时候设成null，存在用单引引起来
                                if($v[11]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $v[11]=preg_replace('/\'/', "''", $v[11]);
                                    $cond.="'"."$v[11]"."',";
                                }
                                if($v[13]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $v[13]=preg_replace('/\'/', "''", $v[13]);
                                    $cond.="'"."$v[13]"."',";
                                }
                                if($v[12]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $v[12]=preg_replace('/\'/', "''", $v[12]);
                                    $cond.="'"."$v[12]"."',";
                                }
                                if($v[6]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $cond.="'"."$v[6]"."',";
                                }
                                if($v[7]=='')
                                {
                                    $cond.="null,$v[8],";
                                }else
                                {
                                    $cond.="'"."$v[7]"."',$v[8],";
                                }
                                if($v[9]=='')
                                {
                                    $cond.="null,$v[10],";
                                }else
                                {
                                    $cond.="'"."$v[9]"."',$v[10],";
                                }
                                if($v[15]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $v[15]=preg_replace('/\'/', "''", $v[15]);
                                    $cond.="'"."$v[15]"."',";
                                }
                                if($v[14]=='')
                                {
                                    $cond.="null,";
                                }else
                                {
                                    $v[14]=preg_replace('/\'/', "''", $v[14]);
                                    $cond.="'"."$v[14]"."',";
                                }
                                if($v[16]=='')
                                {
                                    $cond.="null),";
                                }else
                                {
                                    $v[16]=preg_replace('/\'/', "''", $v[16]);

                                    if ($v[16]=='未公告') $v[16]=0;
                                    if ($v[16]=='已初审') $v[16]=1;
                                    if ($v[16]=='已注册') $v[16]=2;
                                    if ($v[16]=='已无效') $v[16]=3;

                                    $cond.="$v[16]),";
                                }

                                //生成一条sql
                                $condAll.=$cond;
                            }

                            //$ggt=1是初审，$ggt=2是注册
                            if ($ggt===1)
                            {
                                $sql="insert into {$this->issueNo}_CHUSHEN_DETAIL VALUES $condAll";

                            }elseif ($ggt===2)
                            {
                                $sql="insert into {$this->issueNo}_ZHUCE_DETAIL VALUES $condAll";

                            }else
                            {
                                exit;
                            }

                            $sql=rtrim($sql,',');

                            $res=$link->query($sql);

                            if ($res==false)
                            {
                                $this->ImpStatus=true;
                                $this->findErrSql($link,$sql);
                            }else
                            {

                            }
                        }

                        //这里算一次excel源文件，插入GOODS表
                        $this->insertData2Goods($ggt,$AgentId,$excelUrl_arr['excelUrl']);
                    }
                }
            }

            $ann=$ggt===1 ? '初审' : '注册';

            Log4Cron::getSingleton()->writelog($this->issueNo,['code'=>'1','msg'=>"{$this->issueNo}期，公告类型：{$ann}，导入完毕",'time'=>date('Y-m-d_H:i:s',time())]);

            $this->unlockSchedule($ggt);

            $ggt++;
        }






        return true;
    }






}

//获得传入参数
//import delete
$args['action']=isset($argv[1]) ? $argv[1] : '';

//0全部，1初审，2注册
$args['type']=isset($argv[2]) ? $argv[2] : '';

//1620
$args['issueNo']=isset($argv[3]) ? $argv[3] : '';

//指定代理机构
$args['AgName']=isset($argv[4]) ? $argv[4] : '';

$run=new updateAgentInfo;

//检查参数是否正确
if (!$run->checkArgs($args)) exit;

//检查是否可以执行本次操作
if (!$run->checkDataStatus()) exit;

//区分一下要执行什么操作
if ($run->action=='delete')
{
    echo "delete\n";
    //删除操作执行完成，exit，不继续往下了
    exit;
}

//以下是导入操作
//检查是否有正在导入的任务
if (!$run->checkSchedule()) exit;

//创建数据表
$run->createTable();

//区分一下要单个代理机构导入，还是整期导入
if ($args['AgName']!='')
{
    //单个代理机构导入
    //if (!$run->importOneAgent()) exit;
    //if (!$run->importOneData()) exit;

}else
{
    //整期导入
    if (!$run->importAgent()) exit;
    if (!$run->importData()) exit;
}

