<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.1
 * Time: 下午 1:24
 */
//启用session
session_start();
if(isset($_POST['event_id']) &&  isset($_SESSION['user']))
{
    //确保ID为整数
    $id=preg_replace( '/[^0-9]/','',$_POST['event_id']);

    //若不是整数,重定向到主页面
    if(empty($id))
    {
        header("Location:./");
       return;
    }
}
else
{
    //若没有id,重定向到主页面
    header("Location:./");
    exit;
}


//初始化文件
include_once  '../sys/core/init.inc.php';

//输出页头
$page_title="浏览活动";
$css_files=array("style.css","admin.css");
include_once 'assets/common/header.inc.php';

//载入日历
$cal=new Calendar($dbo);
$markup=$cal->confirmDelete($id);
?>

<div id="content">
    <?php echo $markup ?>
    <a href="./">&laquo;返回日历</a>
</div> <!-- end #content --!>

<?php
//输出页尾
include_once 'assets/common/footer.inc.php';
?>
