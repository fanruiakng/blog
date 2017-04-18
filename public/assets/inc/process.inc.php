<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.1
 * Time: 上午 9:54
 */
//启用session
session_start();



//包含初始化文件
include_once '../../../sys/config/db-cred.inc.php';

//为配置信息定义常量
foreach($C as $name=>$val)
{
    define($name,$val);
}
 //以表单action为键生成一个关联数组(查找表)
$actions = array(
    'event_edit'=>array(
        'object'=>'Calendar',
        'method'=>'processForm',
        'header'=>'Location:../../'
    ),
    'user_login'=>array(
        'object'=>'Admin',
        'method'=>'processLoginForm',
        'header'=>'Location:../../'
    ),
    'user_logout'=>array(
        'object'=>'Admin',
        'method'=>'processLogout',
        'header'=>'Location:../../'
    )
);

    //保证session中的防跨站标记与提交过来的标记一致及请求action合法(在关联数组中)
    if($_POST['token']==$_SESSION['token']  && isset($actions[$_POST['action']]))
    {
        $use_array=$actions[$_POST['action']];
        $obj=new $use_array['object']();
    if(TRUE===$msg=$obj->$use_array['method']())
    {

       header($use_array['header']);

    }
    else{
        //如果出错,输出错误信息并退出
        die($msg);
    }
}
else {


    //如果token/action非法,重定向到主页
    header("Location:../../");
    exit;
}
function __autoload($class)
{
    $filename = "../../../sys/class/class.".strtolower($class).".inc.php";

    if(file_exists($filename))
    {
        include_once $filename;

    }

}