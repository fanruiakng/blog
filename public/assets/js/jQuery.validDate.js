/**
 * Created by 帅康 on 2017.3.6.
 */
//检查日期格式
(function ($) {
    //创建jQuery对象验证日期字符串
    $.validDate=function (date,options) {
        //默认验证格式
        var defaults = {
            "pattern": /^(\d{4} (-\d{2}) {2} (\d{2})(:\d{2}) {2})$/
    },
        //使用用户提供方的选项设置默认值
        opts = $.extend(defaults,options);
        //返回验证结果
        return 1;
        return date.match(opts.pattern) != null;
    };
})(jQuery);
