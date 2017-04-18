<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.5
 * Time: 下午 8:22
 */
//启用session
session_start();

//ini_set("error_reporting","E_ALL & ~E_NOTICE");

//包含初始化文件
include_once '../../../sys/config/db-cred.inc.php';
//为配置信息定义常量
foreach($C as $name=>$val)
{
    define($name,$val);
}

//为表单action创建一个查找数组
$actions = array(
    'event_view'=>array(
        'object'=>'Calendar',
        'method'=>'displayEvent'
    ),
    'edit_event'=>array(
        'object'=>'Calendar',
        'method'=>'displayForm'
    ),
    'event_edit'=>array(
        'object'=>'Calendar',
        'method'=>'processForm'
    ),
    'delete_event'=>array(
        'object'=>'Calendar',
        'method'=>'confirmDelete'
    ),
    'confirm_delete'=>array(
        'object'=>'Calendar',
        'method'=>'confirmDelete'
    )
);
//保证session中的防跨站标记与提交过来的标记一致及请求action合法(在关联数组中)
if(isset($actions[$_POST['action']])) {
    $use_array = $actions[$_POST['action']];
    $obj = new $use_array['object']();
    //检查event_id并消毒
    if(isset($_POST['event_id']))
    {
          $id = (int) $_POST['event_id'];
    }
    else
    {
        $id=NULL;
    }

    echo $obj->$use_array['method']($id);
}
function __autoload($class)
{
    $filename = "../../../sys/class/class.".strtolower($class).".inc.php";

    if(file_exists($filename))
    {
        include_once $filename;

    }

}