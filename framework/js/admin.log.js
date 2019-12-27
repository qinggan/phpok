/**
 * 后台日志涉及到的操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这条日志吗？'),function(){
				var url = get_url('log','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		},
		delete30:function()
		{
			$.dialog.confirm(p_lang('确定要删除30天之前日志吗？'),function(){
				var url = get_url('log','delete','date=30');
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		},
		delete_selected:function()
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('未选择要删除的日志'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要删除选中的日志吗？'),function(){
				var url = get_url('log','delete','ids='+$.str.encode(ids));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		}
	}
})(jQuery);

$(document).ready(function(){
	var laydate = layui.laydate;
	//执行一个laydate实例
	laydate.render({
		elem: '#start_date',
	});
	laydate.render({
		elem: '#stop_date',
	});
});