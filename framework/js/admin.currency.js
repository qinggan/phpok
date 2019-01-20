/**
 * 货币管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
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
		}
	}
})(jQuery);

function set_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("currency","sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#taxis_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$.phpok.reload();
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function check_save()
{
	var title = $("#title").val();
	if(!title)
	{
		$.dialog.alert("货币名称不能为空");
		return false;
	}
	var code =$("#code").val();
	if(!code)
	{
		$.dialog.alert("货币标识不能为空");
		return false;
	}
	if(code.length != '3')
	{
		$.dialog.alert("标识只支持三位数");
		return false;
	}
	return true;
}

function currency_del(id,title)
{
	$.dialog.confirm("确定要删除货币：<span class='red'>"+title+"</span>，删除操作可能会给现有产品信息货币计算带来错，请慎用！",function(){
		var url = get_url('currency','delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.dialog.alert("货币：<span class='red'>"+title+"</span> 删除成功",function(){
				$.phpok.reload();
			});
		}
		else
		{
			if(!rs.content) rs.content = '删除失败';
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function update_taxis(val,id)
{
	var url = get_url("currency","sort","sort["+id+"]="+val);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}
$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('请填写新的排序'),function(val){
			if(val != oldval){
				update_taxis(val,id);
			}
		},oldval);
	});
});