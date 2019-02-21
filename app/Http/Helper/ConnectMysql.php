<?php

namespace App\Http\Helper;

class ConnectMysql
{
    //mysql地址
    public $host='127.0.0.1';
    //mysql用户名
    public $username='root';
    //mysql密码
    public $password='root';
    //mysql数据库名
    public $db='en_web';

    //mysqli对象
    private static $mysqli;
    //PDO对象
    private static $PDO;

    //保存单例对象
    private static $ins;

    //创建对象
    public function getSingleton()
    {
        if (self::$ins instanceof self)
        {
            return self::$ins;
        }

        return self::$ins=new self();
    }

    //禁止创建
    private function __construct()
    {
        self::$mysqli=$this->ConnWithMysqli();

        self::$PDO=$this->ConnWithPDO();
    }

    //释放资源
    public function __destruct()
    {
        $this->CloseMysqli();
        $this->ClosePDO();
    }

    //禁止克隆
    private function __clone()
    {
    }

    //mysqli方式连接
    public function ConnWithMysqli()
    {
        if (self::$mysqli!=null) return self::$mysqli;

        self::$mysqli=mysqli_connect($this->host,$this->username,$this->password,$this->db) or die('mysql链接失败');

        if (empty(self::$mysqli))
        {
            return false;
        }

        mysqli_set_charset(self::$mysqli,'utf8mb4');

        return self::$mysqli;
    }

    //关闭mysqli资源
    public function CloseMysqli()
    {
        if (self::$mysqli==null) return true;

        mysqli_close(self::$mysqli);
        self::$mysqli=null;

        return true;
    }

    //PDO方式连接
    public function ConnWithPDO()
    {
        if (self::$PDO!=null) return self::$PDO;

        self::$PDO=new \PDO("mysql:host=$this->host;dbname=$this->db","$this->username","$this->password");

        if (empty(self::$PDO))
        {
            return false;
        }

        self::$PDO->query('SET NAMES utf8mb4');

        return self::$PDO;
    }

    //关闭PDO资源
    public function ClosePDO()
    {
        if (self::$PDO==null) return true;

        self::$PDO=null;

        return true;
    }
}

