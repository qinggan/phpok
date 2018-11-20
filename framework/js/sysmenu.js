/***********************************************************
	Filename: {phpok}/js/sysmenu.js
	Note	: 核心配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-05 22:43
***********************************************************/
//设置状态
function set_status(id)
{
	var url = get_url("system","status") + '&id='+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		if(!rs.content) rs.content = '0';
		var oldvalue = $("#status_"+id).attr("value");
		var old_cls = "status"+oldvalue;
		$("#status_"+id).removeClass(old_cls).addClass("status"+rs.content);
		$("#status_"+id).attr("value",rs.content);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//更新内容状态
function appfile_set(val)
{
	if(val == "list")
	{
		$("#list_set").show();
	}
	else
	{
		$("#list_set").hide();
	}
}


function delete_sysmenu(id,title)
{
	$.dialog.confirm("确定要删除导航：<span class='red'>"+title+"</span>，删除后是不能恢复的！",function(){
		var url = get_url('system','delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status != 'ok')
		{
			$.dialog.alert(rs.content);
			return false;
		}
		window.location.reload();
	});
}