/***********************************************************
	Filename: {phpok}/js/currency.js
	Note	: 货币管理中涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月13日
***********************************************************/
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