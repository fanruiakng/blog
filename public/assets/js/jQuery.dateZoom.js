/**
 * Created by 帅康 on 2017.3.6.
 */
//检查日期格式
(function ($) {
    //鼠标悬停时放大元素文本,离开时回复  ,挂载到fn来支持链式调用
    $.fn.dateZoom=function (options) {
        //只有明确覆盖的参数才以options传入
        var opts = $.extend($.fn.dateZoom.defaults,options);

        //便利每个匹配元素,返回修改后的jQuery对象,已维护链式调用
        return this.each(function () {
            //保存元素字体的原始大小
            var originalsize = $(this).css("font-size");

            //为hover事件绑定函数,第一个在指针一直是出发,第二个在指针离开时出发
            $(this).hover(function () {
                $.fn.dateZoom.zoom(this,opts.fontsize,opts);
            },function () {
                $.fn.dateZoom.zoom(this,originalsize,opts);
                });

        });

    };

    //插件默认值
    $.fn.dateZoom.defaults = {
        "font-size":"110%",
        "easing":"swing",
        "duration":"600",
        "callback":null
    };

    //使用函数,用户在插件之外使用
    $.fn.dateZoom.zoom = function (element,size,opts) {
        $(element).animate({
            "font-size":size
        },{
            "duration":opts.duration,
            "easing":opts.easing,
            "callback":opts.callback
        })
            .dequeue()//放置动画跳跃
            .clearQueue();//保证同一时间只执行一个动画
    }
})(jQuery);
