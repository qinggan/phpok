/***********************************************************
	Filename: {phpok}/js/user.js
	Note	: 会员管理中涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月2日
***********************************************************/
//检查添加操作
function check_add()
{
	var url = get_url("user","chk");
	var id = $("#id").val();
	if(id && id != "undefined")
	{
		url += "&id="+id;
	}
	var user = $("#user").val();
	if(!user || user == "undefined")
	{
		$.dialog.alert("会员账号不能为空");
		return false;
	}
	url += "&user="+$.str.encode(user);
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

function del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此信息吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = get_url("user","ajax_del") + "&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			window.location.reload();
		}
		else
		{
			alert(msg);
			return false;
		}
	}
}

//更改权限状态
function set_status(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var t = $("#status_"+id).attr("value");
	if(t == 2)
	{
		$.dialog.alert("此会员已被锁定，请点编辑后进行解除锁定");
		return false;
	}
	var url = get_url("user","ajax_status") + "&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id).removeClass("status"+t).addClass("status"+n_t);
		$("#status_"+id).attr("value",n_t);
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}
