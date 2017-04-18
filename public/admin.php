<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.1
 * Time: 上午 4:45
 */

//ini_set("error_reporting","E_ALL & ~E_NOTICE");

//初始化文件
include_once  '../sys/core/init.inc.php';
//检查登录
if (!isset($_SESSION['user']))
{
    header("Location:./");
    exit;
}
//输出页头
$page_title="添加活动";
$css_files=array("style.css","admin.css");
include_once 'assets/common/header.inc.php';

//载入日历
$cal=new Calendar($dbo);
?>

<div id="content">
    <?php echo $cal->displayForm(); ?>
</div> <!-- end #content --!>

<?php
//输出页尾
include_once 'assets/common/footer.inc.php';
?>
