<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.2.25
 * Time: 上午 9:07
 */
/**
 * 启用session ,防范跨站请求
 *
 */
session_start();

/*
 * 如果session没有防跨站请求标记则生成一个
 */
if(!isset($_SESSION['token']))
{
    $_SESSION['token']=sha1(uniqid(mt_rand(),TRUE));
}

//包含必须的配置信息
 include_once "../sys/config/db-cred.inc.php";
//为配置信息定义常量
foreach ($C as $name=>$val)
{
    define($name,$val);
}

//生成一个PDO对象
$dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME;
$dbo=new PDO($dsn,DB_USER,DB_PASS);

//定义自动载入类的__autoload函数
function __autoload($class)
{
    $filename = "../sys/class/class.".strtolower($class).".inc.php";

    if(file_exists($filename))
    {
        include_once  $filename;

    }

}
