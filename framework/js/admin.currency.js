/**
 * 货币管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年11月25日
**/

;(function($){
	$.admin_currency = {
		status:function(id)
		{
			$.phpok.json(get_url("currency","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		save:function(obj)
		{
			var url = get_url('currency','setok');
			var lock = $.dialog.tips('正在提交中，请稍候…',100).lock();
			$.phpok.submit($(obj)[0],url,function(rs){
				if(!rs.status){
					lock.content(rs.info).time(2);
					return false;
				}
				lock.setting('close',function(){
					$.admin.close(get_url('currency'));
				});
				lock.content('数据保存成功').time(2);
				return true;
			});
			return false;
		},
		del:function(id,title)
		{
			var tip = p_lang('确定要删除货币 {title}，请慎用','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url('currency','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('删除成功，请稍候…'),function(){
						$.phpok.reload();
					}).lock();
				});
			});
		},
		sort:function(id,val)
		{
			var url = get_url("currency","sort","sort["+id+"]="+val);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('修改成功',function(){
					$.phpok.reload();
				}).lock();
			});
		}
	}
})(jQuery);

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('请填写新的排序'),function(val){
			if(val != oldval){
				$.admin_currency.sort(id,val);
			}
		},oldval);
	});
});