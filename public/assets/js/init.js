/**
 * Created by 帅康 on 2017.3.5.
 */
jQuery(function ($) {

    //服务器处理ajax的文件
    var processFile = "assets/inc/ajax.inc.php";

    //维护模态框的功能函数
    var fx={
        //检查模态框是否存在,有则返回,无则生成
        "initModal":function () {
            //如果没有元素匹配,则长度属性置0
            //property 返回 0
            if($(".modal-window").length==0)
            {
                //创建一个div元素,创建div,追加至body中
                return $("<div>")
                    .addClass("modal-window")
                    .appendTo("body");
            }
            else
            {
                //模态框已存在,返回
                return $(".modal-window");
            }

        },

        //将窗口添加到标记文件并让它淡出
        "boxin":function (data,modal) {
            //创建覆盖层,并添加class和时间处理函数,追加至body
            $("<div>")
                .hide()
                .addClass("modal-overlay")
                .click(function (event) {
                    //删除活动
                    fx.boxout(event);
                })
                .appendTo("body");

            //将数据载入并追加至body
            modal
                .hide()
                .append(data)
                .appendTo("body");

            //淡入淡出窗口和覆盖层
            $(".modal-window,.modal-overlay")
                .fadeIn("slow");
        },
        //淡出窗口并从DOM删除
        "boxout":function (event) {
            //如果该函数用作事件处理函数,阻止其默认行为
            if(event!=undefined)
            {
                event.preventDefault();
            }

            //从所有的连接中删除active class
            $("a").removeClass("active");

            //淡出并删除
            $(".modal-window,.modal-overlay")
                .fadeOut("slow",function () {
                    $(this).remove();
                });
        },

        //jiang新活动无刷新添加到日历
        "addevent":function (data,formData) {
            //将查询字符串转为对象
            var entry = fx.deserialize(formData),

            //为当前月生成一个date对象
            cal = new Date(NaN),

                //为新活动生成一个data对象
                event = new Date(NaN),

                //从H2元素的ID属性中提取日历月份
                cdata = $("h2").attr("id").split("-"),

                //提取时间日期,月份和年份
                date = entry.event_start.split(" ")[0],

                //将活动数据拆分到数组
                edate = date.split('-');

            //设定cal日期对象的值
            cal.setFullYear(cdata[1],cdata[2],1);

            //设定event日期对象的值
            event.setFullYear(edate[0],edate[1],edate[2]);

            //修正时区差异
            event.setMinutes(event.getTimezoneOffset());

            //如果年份和月份都相符,开始添加活动
            if(cal.getFullYear()==event.getFullYear() && cal.getMonth()==event.getMonth())
            {
                //得到活动是该月第几天
                var d = event.getDate()+1;
                var day = String(d);

                //若日期只有一位,追加0
                day = day.length==1 ? "0"+day :day;

                //添加新活动连接
                $("<a>")
                    .hide()
                    .attr("href","view.php?event_id="+data)
                    .text(entry.event_title)
                    .insertAfter($("strong:contains("+day+")"))
                    .delay(1000)
                    .fadeIn("slow");
            }

        },

        //反序列化查询字符串,并返回一个event对象
        "deserialize":function (str) {
            //chai分键值对
            var data = str.split("&"),

                //sheng明循环变量
                pairs=[],entry={},key,val;

            //便利键值对
            for(x in data)
            {
                //键值对化数组
                pairs = data[x].split("=");

                //第一个是name
                key= pairs[0];

                //di er ge是value
                val = pairs[1];

                //保存键值对
                entry[key] = fx.urldecode(val);
            }
            return entry;
        },

        //对url查询字符串解码
        "urldecode":function (str) {
            //加号置空格
            var converted = str.replace(/\+/g,' ');

            //解码其他字符
            return decodeURIComponent(converted);
        },

        //活动删除之后,移除相关html
        "removeevent":function () {
            //删除所有拥有class active 的活动
            $(".active")
                .fadeOut("slow",function () {
                    $(this).remove();
                })
        }
    };

    //为dateZoom设置默认字体大小
    $.fn.dateZoom.defaults.fontsize = "13px";
$("li>a").dateZoom();


    //点击标题时,将活动信息显示在模态框
    $(document).on("click","li>a",function(event){
        //组织连接载入view.php
        event.preventDefault();

        //未连接添加active class
        $(this).addClass("active");

        //从连接的href属性中得到查询字符创
        var data = $(this)
            .attr("href")
            .replace(/.+?\?(.*)$/,"$1"),

        //检查模态框,存在则选中,否则创建一个
        modal = fx.initModal();

        //创建一个用来关闭窗口的按钮
        $("<a>")
            .attr("href","#")
            .addClass("modal-close-btn")
            .html("&times;")
            .click(function (event) {
                //删除
                fx.boxout(event);
            })
            .appendTo(modal);

        //从数据库载入活动文件
        $.ajax({
            type:"post",
            url:processFile,
            data:"action=event_view&"+data,
            success:function (data) {
                //注意现在的活动数据
                fx.boxin(data,modal);
            },
            error:function (msg) {
                modal.append(msg);
            }
        });

    });

    //发布活动模态框
    $(document).on("click",".admin,.admin-options form",function (event) {
        //阻止表单提交
        event.preventDefault();

        //ajax请求的action
        var action =$(event.target).attr("name") || "edit_event",

        //将input元素中的event_id提取
        id = $(event.target)
            .siblings("input[name=event_id]")
            .val(),

        //检查id并添加
        id= (id!=undefined) ? "&event_id="+id : "";

        //载入修改表单并显示
        $.ajax({
            type:"POST",
            url:processFile,
            data:"action="+action+id,
            success:function (data) {
                //隐藏表单
                var form = $(data).hide(),
                    //确保模态框存在
                    modal=fx.initModal()
                        .children(":not(.modal-colse-btn)")
                        .remove()
                        .end();

                //创建模态框并淡入
                fx.boxin(null,modal);

                //将表单载入,淡入内容,添加class
                form
                    .appendTo(modal)
                    .addClass("edit-form")
                    .fadeIn("slow");
            },
            error:function (msg) {
                alert(msg);
            }
        });
    });

    //修改活动模态框
    $(document).on("click",".edit-form input[type=submit]",function (event) {
        //阻止表单提交
        event.preventDefault();

       //序列化表单数据
        var formData = $(this).parents("form").serialize(),

        //保存提交按钮的值
        submitVal = $(this).val(),

        //remove确定是否需要删除
        remove = false,

        //分别保存开始结束日期
        start = $(this).siblings("[name=event_start]").val(),
            end= $(this).siblings("[name=event_start]").val();


            //若是删除表单,追加action
        if($(this).attr("name")=="confirm_delete")
        {
            //在追加字符串中追加必要信息
            formData+= "&action=confirm_delete"+ "&confirm_delete="+submitVal;
            //如果活动将要删除,添加一个标记
            if(submitVal=="Yes")
            {
                remove = true;
            }
        }

        //创建/修改活动前检查日期
        if($(this).siblings("[name=action]").val()=="event_edit")
        {
            if(!$.validDate(start) ||!$.validDate(end))
            {
                alert ("pig!write like this:0000-00-00 00:00:00");
                return false;
            }
        }

        //jiang表单数据送往处理程序
        $.ajax({
            type:"POST",
            url:processFile,
            data:formData,
            success:function (data) {
                //如果活动简要删除,在标记中删除
                if(remove==true)
                {
                    fx.removeevent();
                }

                //淡出模态窗口
                fx.boxout();

                if($("[name=event_id]").val().length==0 && remove==false) {
                    //添加活动
                    fx.addevent(data, formData);
                }
            },
            error:function (msg) {
                alert(msg);
            }
        });
    });

    //为cancel添加行为
    $(document).on("click",".edit-form a:contains(cancel)",function (event) {
        fx.boxout(event);
    })
});
