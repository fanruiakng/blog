<?php
/**
 * 创建并维护活动日程表
 *
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.2.25
 * Time: 上午 8:29
 */


class Calendar extends DB_Connect
{
    //属性定义
    /**
     * 日历根据此日期构建
     *
     * 格式为0000-00-00 00:00:00
     * @var string 日历显示日期
     */
    private $_useDate;

    /**
     * 日历显示月份
     *
     * @var int 月份
     */
    private $_m;


    /**
     * 当前显示月份是哪一年
     *
     * @var int 当前年份
     *
     */
    private $_y;


    /**
     * 这个月有多少天
     *
     * @var int  这个月天数
     */
    private $_daysInMonth;

    /**
     *  这个月起始是周几
     *
     * @var int 这个月从周几开始
     */

    private $_startDay ;


    //方法定义

    /**
     * 数据库对象储存有关的数据
     *
     * 此类接受一个数据库对象参数.有效则存入$_db中,否则生成一个新对象存起来
     *
     * 此方法收集并存储的信息:当前月份 该月多少天 该月从周几开始 当前日期
     *
     * @param object $dbo数据库对象
     * @param string $useDate 生成日历使用的日期
     * @return void
     */

    public function __construct($dbo=NULL,$useDate=NULL)
{
    /**
     * 调用父类构造函数
     * 检查数据库对象
     */

    parent::__construct($dbo);

    //搜集并储存该月有关的数据

    if(isset($useDate))
    {
        $this->_useDate= $useDate;
    }
    else
    {
        $this->_useDate=date('Y-m-d H:i:s');
    }

    //把日期转换为时间戳,确定要显示的年和月
    $ts=strtotime($this->_useDate);
    $this->_m=date('m',$ts);
    $this->_y=date('Y',$ts);

    //确定此月有多少天
    $this->_daysInMonth= cal_days_in_month(
        CAL_GREGORIAN,
        $this->_m,
        $this->_y
    );

    //确定此月从周几开始

    $ts= mktime(0,0,0,$this->_m,1,$this->_y);
    $this->_startDay=date('w',$ts);

}

    /**
     * 得到活动信息html
     * @param int $ID  活动id
     * @return string 用于显示活动信息的基本html标记
     */
    public function displayEvent($id)
    {

        //确保传入有效id
        if(strlen($id)==0)
        {
            return NULL;
        }
        //确保id是个整数
        $id=preg_replace('/[^0-9]/','',$id);

        //从数据库载入活动数据
        $event=$this->_loadEventById($id);

        //为$date,$start,$end变量生成相应的字符串
        $ts=strtotime($event->start);
        $date=date('F d,Y',$ts);
        $start=date('g:ia',$ts);
        $end=date('g:ia',strtotime($event->end));

        //若用户已登录,载入管理选项
        $admin=$this->_adminEntryOptions($id);


        //生成并返回html标记
        return "<h2>$event->title</h2>".
                "\n\t<p class=\"dates\">$date,$start&mdash;$end</p>".
                "\n\t<p>$event->description</p>$admin";

    }

    /**
     * 生成修改或创建或修改活动的表单
     * @return 表单标记字符串
     */
    public function displayForm()
    {
        //检查是否传入活动ID
        if(isset($_POST['event_id']))
        {
            $id=(int) $_POST['event_id']; //强制转换确保数据安全
        }
        else
        {
            $id=NULL;
        }

        /**
         * 标题提交按钮文本
         */
        $submit="Create a New Event";

        //若传入活动id,则载入相应得活动数据
        if(!empty($id))
        {
            $event=$this->_loadEventById($id);
            //若未找到相应的活动,返回NULL
            if(!is_object($event))
            {
                return NULL;
            }

            $submit="Edit This Event";
            return <<<FORM_MARKUP
<form action="assets/inc/process.inc.php" method="post">
    <fieldset>
    <legend>{$submit}</legend>
    <lable for="event_title">Event Title</lable>
    <input type="text" name="event_title" id="event_title" value="$event->title" />
    <lable for="event_start">Start Time</lable>
    <input type="text" name="event_start" id="event_start" value="$event->start"/>
    <lable for="event_end">End Time</lable>
    <input type="text" name="event_end" id="event_end" value="$event->end" />
    <lable for="event_description">Event Description</lable>
    <textarea type="text" name="event_description" id="event_description" >$event->description 
    </textarea>
     
     <input type="hidden" name="event_id" value="$event->id" />
     <input type="hidden" name="token" value="$_SESSION[token]" />
     <input type="hidden" name="action" value="event_edit" />
     <input type="submit" name="event_submit" value="$submit" />
     or <a href="./">cancel</a>
    </fieldset>
</form>

FORM_MARKUP;
        }

        //生成标记

        return <<<FORM_MARKUP
<form action="assets/inc/process.inc.php" method="post">
    <fieldset>
    <legend>{$submit}</legend>
    <lable for="event_title">Event Title</lable>
    <input type="text" name="event_title" id="event_title" />
    <lable for="event_start">Start Time</lable>
    <input type="text" name="event_start" id="event_start" />
    <lable for="event_end">End Time</lable>
    <input type="text" name="event_end" id="event_end"  />
    <lable for="event_description">Event Description</lable>
    <textarea type="text" name="event_description" id="event_description" > 
    </textarea>
     
     <input type="hidden" name="event_id"  />
     <input type="hidden" name="token"  value="$_SESSION[token]"/>
     <input type="hidden" name="action" value="event_edit" />
     <input type="submit" name="event_submit" value="$submit" />
     or <a href="./">cancel</a>
    </fieldset>
</form>

FORM_MARKUP;
    }

    /**
     * 验证表单,保存/更新活动信息
     *
     * @return 成功返回true,失败返回出错误信息
     *
     */
    public function processForm()
    {

        //若action设置不正确,退出
        if($_POST['action']!='event_edit')
        {
            return "接收数据错误!";
        }

        //转义表单提交过来的数据
        $title = htmlentities($_POST['event_title'],ENT_QUOTES);
        $desc = htmlentities($_POST['event_description'],ENT_QUOTES);
        $start = htmlentities($_POST['event_start'],ENT_QUOTES);
        $end = htmlentities($_POST['event_end'],ENT_QUOTES);

        //检查时间格式
        if(!$this->_valiDate($start) ||!$this->_valiDate($end))
        {
            return "you pig!writw like this:0000-00-00 00:00:00";
        }

        //如果提交数据中没有活动id,就创建一个新活动
        if(empty($_POST['event_id']))
        {
            $sql="INSERT INTO events
                    (event_title,event_desc,event_start,event_end)
                    VALUES
                    (:title,:description,:start,:end)";

        }
        //否则更新这个活动
        else
        {
            //为了数据安全,将id强制转换为整数
            $id=(int)$_POST['event_id'];
            $sql = "UPDATE 'events'
                    SET  
                        event_title=:title,
                        event_desc=:description,
                        event_start=:start,
                        event_end=:end 
                        WHERE event_id=$id";
        }
        try
        {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":title",$title,PDO::PARAM_STR);
            $stmt->bindParam(":description",$desc,PDO::PARAM_STR);
            $stmt->bindParam(":start",$start,PDO::PARAM_STR);
            $stmt->bindParam(":end",$end,PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            return $this->db->lastInsertId();
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }

    }

    /**
     * 确认一个活动是否该被删除并执行
     *
     * 单击删除按钮时生成一个确认按钮.确定,则从数据库删除.否则不执行操作.返回主页
     *
     * @param int $id 活动id
     * @return 若确认删除则可能返回null或异常信息,否则返回null
    */
    public function confirmDelete($id)
    {
        //检查ID
        if(empty($id))
        {
            return null;
        }

        //确认ID为整数
        $id=preg_replace('/[^0-9]/','',$id);


        //确认表单被提交一个正确的信号,检查表单提交数据
        if(isset($_POST['confirm_delete']) && $_POST['token']==$_SESSION['token'])
        {
            //若用户确认删除,则从数据库删除此活动
            if($_POST['confirm_delete']=="Yes")
            {
                $sql="DELETE FROM events WHERE event_id=:id LIMIT 1";
                try
                {

                    $stmt=$this->db->prepare($sql);
                    $stmt->bindParam(":id",$id,PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();
                    header("Location:./");
                    return;
                }
                catch (Exception $e)
                {
                    return $e->getMessage();
                }
            }
            else
            {
                header("Location:./");

                return;
            }
        }

        //若用户表单尚未确认,显示它
        $event=$this->_loadEventById($id);

        //若得到的$event非对象,跳至主页
        if(!is_object($event))
        {

            header("Location:./");
        }

        return <<<CONFIRM_DELETE
    <form action="confirmdelete.php" method="post">
<h2>
    Are you sure you want to delete "$event->title"?
</h2>
<p>There is <strong>no undo</strong> if you continue.</p>
<p>
<input type="submit" name="confirm_delete"
    value="Yes" />
<input type="submit" name="confirm_delete"
    value="NO!" />
<input type="hidden" name="event_id"
    value="$event->id" />
<input type="hidden" name="token"
    value="$_SESSION[token]" />
</p>
</form>
CONFIRM_DELETE;
    }

    /**
     * 验证日期字符串
     * @param string $date 要验证的字符串
     * $return 成功返回true 失败返回false
     */
    private function _valiDate($date){
        //定义验证日期格式的正则表达式的模式
        $pattern = '/^(\d{4} (-\d{2}) {2} (\d{2})(:\d{2}) {2})$/';

        return TRUE;
        //进行匹配
        return preg_replace($pattern,$date)==1 ? TRUE : FALSE;
    }

    /**
     * 将活动信息载入一个数组
     *
     * @param int $id 用来过滤结果的可选活动ID
     * @return array 来自数据库的活动信息数组
     */
    public function _loadEventDate($id=NULL)
    {
        $sql="SELECT
                  event_id,event_title,event_desc,event_start,event_end
              FROM events";

        //若有活动ID,则添加WHERE语句,只返回该活动
        if(!empty($id))
        {
            $sql.=" WHERE event_id=:id LIMIT 1";

        }

        //否则载入该月所有的活动
        else
        {
            //找出该月第一天和最后一天
            $start_ts =mktime(0,0,0,$this->_m,1,$this->_y);
            $end_ts =mktime(23,59,59,$this->_m+1,0,$this->_y);
            $start_date=date('Y-m-d H:i:s',$start_ts);
            $end_date=date('Y-m-d H:i:s',$end_ts);

            //找出该月所有活动
            $sql.="  WHERE event_start
                     BETWEEN '$start_date'
                     AND '$end_date'
                   ORDER BY event_start";

        }

        try
        {


            $stmt=$this->db->prepare($sql);

            //若ID有效则绑定次参数
            if(!empty($id))
            {
               $stmt->bindparam(":id",$id,PDO::PARAM_INT);
            }

            $stmt->execute();
            $results = $stmt->fetchALL(PDO::FETCH_ASSOC);

            $stmt->closeCursor();

            return $results;
        }
        catch(Exception $e)
        {
            die ($e->getMessage());
        }
    }


    /**
     * 载入该月全部活动信息到一个数组
     *
     * @return array 活动信息
     */
    private function _createEventObj()
    {
        //载入活动数组
        $arr=$this->_loadEventDate();

        //按照活动发生在该月的第几天将活动数据重新组织到一个新数组中
        $events=array();
        foreach($arr as $event)
        {
            //strtotime 转换为时间戳  再由date转换为本月第几天
            $day=date('j',strtotime($event['event_start']));
            try
            {
                $events[$day][]=new Event($event);
            }
            catch(Exception $e)
            {
                die ($e->getMessage());
            }
        }


        return $events;
    }

    /**
     * 生成用于显示日历和活动的html标记
     * 使用储存在类属性中的数据, 载入给定月份的活动数据,生成并返回完整的日历html标记
     *
     * @return atring 日历html标记
     */
    public function buildCalendar()
    {
        //确定日历显示月份并创建一个用于标识日历每列星期几的缩写数组
        $cal_month=date('F Y',strtotime($this->_useDate));  //F - 月份的完整的文本表示（January[一月份] 到 December[十二月份]）Y - 年份的四位数表示
        $cal_id = date('Y-m',strtotime($this->_useDate));
        $weekdays=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');

        //给日历标记添加一个标题

        $html = "\n\t<h2 id = \"month-$cal_id\">$cal_month</h2>";
        for($d=0,$labels=NULL;$d<7;++$d)
        {
            $labels.="\n\t\t<li>".$weekdays[$d]."</li>";
        }
        $html.="\n\t<ul class=\"weekdays\">".$labels."\n\t</ul>";

        //载入活动数据
        $events=$this->_createEventObj();


        //生成日历html标记
        $html.="\n\t<ul>";//  开始一个新的<ul>
        //date:j 一个月的第几天 m 月份 Y 年份
        for($i=1.,$c=1,$t=date('j'),$m=date('m'),$y=date('Y');
        $c<=$this->_daysInMonth;
        ++$i)
        {
            //为起始日之前的几天添加class fill
            $class = $i<=$this->_startDay ? "fill" :NULL;

            //如果处理日期时间天 ,则为它添加class today
            if( $c ==$t  &&  $m==$this->_m  && $y==$this->_y)
            {
                $class = "today";
            }


            //生成列表<li>的开始和结束标记
            $ls=sprintf("\n\t\t<li class=\"%s\">",$class);
            $le="\n\t\t</li>";


            //添加日历的主体,内容是该月的每一天 及活动
            if($this->_startDay<$i  && $this->_daysInMonth>=$c)
            {
                //格式或活动数据
                $event_info= NULL;
                if(isset($events[$c]))
                {
                    foreach($events[$c] as $event)
                    {
                        $link = '<a href="view.php?event_id='.$event->id.'">'.$event->title.'</a>';
                        $event_info.="\n\t\t\t$link";
                    }
                }

                $date= sprintf("\n\t\t\t<strong>%02d</strong>",$c++);
            }
            else
            {
                $date="&nbsp;";
            }

            //如果是周六,就新起一行
            $warp=$i!=0 && $i%7==0 ? "\n\t</ul>\n\t<ul>" :NULL;


            //组装上述碎片
            $html .=$ls.$date.$event_info.$le.$warp;
        }
        //为最后一周的几天添加填充项
        while($i%7!=1)
        {
            $html.="\n\t\t<li class=\"fill\">&nbsp;</li>";
            ++$i;
        }

        //关闭最后一个标签
        $html.="\n\t</ul>\n\n";

        /**
         * 若用户登录,显示管理选项
         */
        $admin=$this->_adminGeneralOptions();

        //返回用于输出html
        return $html.$admin;
    }

    /**
     * 根据活动ID得到event对象
     *
     * @param int $ID 活动id
     * @return object 活动对象
     */
     private function _loadEventById($id)
     {
         //如果id为空,返回NULL
         if(empty($id))
         {
             return NULL;
         }

         //载入活动信息数组
         $event=$this->_loadEventDate($id);

         //返回event对象
         if(isset($event[0]))
         {
             return new Event($event[0]);
         }
         else
         {
             return NULL;
         }
     }

     /**
      * 生成管理连接的html
      * @return 字符串标记显示管理连接
      */
     private function _adminGeneralOptions()
     {
         /**
          * 若已登录,显示管理界面
          */
         if(isset($_SESSION['user'])) {
             return <<<ADMIN_OPTIONS
    <a href="admin.php" class="admin">+ 添加活动</a>
    <form action="assets/inc/process.inc.php" method="post">
        <div>
            <input type="submit" value="登出"  />
            <input type="hidden" name="token"
                value="$_SESSION[token]" />
            <input type="hidden" name="action"
                    value="user_logout"/>
        </div>
    </form>
ADMIN_OPTIONS;
         }
         else
         {
             return <<<ADMIN_OPTIONS
    
ADMIN_OPTIONS;
         }

     }

     /**
      * 为给定活动id生成修改和删除按钮
      *
      * @param int $id 活动id
      * @return string 修改删除选项标记
      */
     private  function _adminEntryOptions($id)
     {
         if(isset($_SESSION['user'])) {
             return <<<ADMIN_OPTIONS
    <div class="admin-options">
        <form action="admin.php" method="post">
            <p>
                <input type="submit" name="edit_event"
                        value="编辑活动" />
                <input type="hidden" name="event_id"
                        value="$id" />
            </p>
        </form>
        <form action="confirmdelete.php" method="post">
            <p>
                <input type="submit" name="delete_event"
                        value="删除活动" />
                <input type="hidden" name="event_id"
                        value="$id" />
            </p>
        </form>
    </div><!-- end .admin-options -->
ADMIN_OPTIONS;
         }
         else
         {
             return NULL;
         }
     }
}

