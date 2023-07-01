/**
 * 后台日志涉及到的操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年05月07日
**/
;(function($){
	$.admin_log = {
		search:function(name,val)
		{
			if(name == 'start_time'){
				$("input[name=start_time]").val(val);
				$("input[type=submit][class=submit2]").click();
			}
		},
		download:function()
		{
			var start_time = $("input[name=start_time]").val();
			var url = get_url("log","download");
			if(start_time){
				url += "&start_time="+$.str.encode(start_time);
			}
			$.phpok.go(url);
		}
	}
})(jQuery);

$(document).ready(function(){
	var laydate = layui.laydate;
	//执行一个laydate实例
	laydate.render({
		elem: '#start_date',
	});
});