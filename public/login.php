<?php
/**
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.2
 * Time: 上午 6:14
 */

ini_set("error_reporting","E_ALL & ~E_NOTICE");
//包含初始化文件
include_once '../sys/core/init.inc.php';


//初始化标题和样式文件
$page_title="登录";
$css_files=array('style.css','admin.css');

//包含页头
include_once 'assets/common/header.inc.php';

?>

<div id="content">
    <form action="assets/inc/process.inc.php" method="post">
        <fieldset>
            <label >密码</label>
            <input type="password" name="pass" id="pass" value="" />
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
            <input type="hidden" name="action" value="user_login" />
            <input type="submit" name="login_submit" value="登录" />
            or<a href="./">取消</a>
        </fieldset>
    </form>
</div><!-- end #content -->


<?php
//包含页尾
include_once 'assets/common/footer.inc.php';
?>
