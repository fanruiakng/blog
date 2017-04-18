<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.2.25
 * Time: 上午 9:23
 */

//ini_set("error_reporting","E_ALL & ~E_NOTICE");

//包含初始化文件
include_once '../sys/core/init.inc.php';

//载入1月份日历

$cal=new Calendar($dbo);

//初始化标题和样式文件
$page_title="日历";
$css_files=array('style.css','admin.css','ajax.css');

//包含页头

include_once 'assets/common/header.inc.php';

?>
<a href="login.php">登录</a>
<div id="content">
    <?php
        //生成并显示日历html
        echo $cal->buildCalendar();
    ?>
</div><!-- end #content -->


<?php
    //包含页尾
        include_once 'assets/common/footer.inc.php';
?>
