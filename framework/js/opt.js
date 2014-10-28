/***********************************************************
	Filename: js/opt.js
	Note	: 选项组用到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-02 20:16
***********************************************************/
var base_url = basefile + "?" + ctrl_id + "=opt&"+func_id+"=";

//添加选项组
function add_opt_group()
{
	var t = $("#title_0").val();
	if(!t)
	{
		alert("名称不能为空！");
		return false;
	}
	var url = base_url + "group_save&title="+$.str.encode(t);
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		$.phpok.reload();
	}
	else
	{
		if(!msg) msg = "添加失败！";
		alert(msg);
		return false;
	}
}

//更新选项组
function update_opt_group(id)
{
	var t = $("#title_"+id).val();
	if(!t)
	{
		alert("名称不能为空！");
		return false;
	}
	var url = base_url + "group_save&title="+$.str.encode(t)+"&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert("选项组更新成功！");
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "更新失败！";
		alert(msg);
		return false;
	}
}

//删除选项组
function delete_opt_group(id)
{
	var t = $("#title_"+id).val();
	if(!t)
	{
		var qc = confirm("确定要删除此选项组吗？");
	}
	else
	{
		var qc = confirm("确要定删除选项组："+t+"，删除后相应的数据也会删除，请慎用！");
	}
	if(qc == '0') return false;
	var url = base_url + "group_del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert("选项组信息删除成功！");
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "选项组删除失败！";
		alert(msg);
		return false;
	}
}

//跳转到指定选项组内容列表
function opt_list(id)
{
	var url = base_url+"list&group_id="+id;
	direct(url);
}

//添加选项内容
function add_opt(gid)
{
	if(!gid || gid == "0")
	{
		alert("未指定选项组！");
		return false;
	}
	var url = base_url+"add&group_id="+gid;
	//判断是否有父ID
	var pid = $("#parent_0").val();
	url += "&pid="+pid;
	//值
	var v = $("#val_0").val();
	if(!v)
	{
		alert("值不能为空！");
		return false;
	}
	url += "&val="+$.str.encode(v);
	//显示
	var s = $("#title_0").val();
	if(!s)
	{
		alert("显示信息不能为空，您可以设置成与值一样！");
		return false;
	}
	url += "&title="+$.str.encode(s);
	var taxis = $("#taxis_0").val();
	if(taxis)
	{
		url += "&taxis="+$.str.encode(taxis);
	}
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "内容添加失败！";
		alert(msg);
		return false;
	}
}

//更新选项内容
function update_opt(id)
{
	if(!id)
	{
		alert("操作异常，没有指定要更新的内容ID！");
		return false;
	}
	var url = base_url+"edit&id="+id;
	//值
	var v = $("#val_"+id).val();
	if(!v)
	{
		alert("值不能为空！");
		return false;
	}
	url += "&val="+$.str.encode(v);
	//显示
	var s = $("#title_"+id).val();
	if(!s)
	{
		alert("显示信息不能为空，您可以设置成与值一样！");
		return false;
	}
	url += "&title="+$.str.encode(s);
	var taxis = $("#taxis_"+id).val();
	if(taxis)
	{
		url += "&taxis="+$.str.encode(taxis);
	}
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert('更新成功');
	}
	else
	{
		if(!msg) msg = "内容更新失败！";
		alert(msg);
		return false;
	}
}

//删除选项内容
function delete_opt(id)
{
	var qc = confirm("确定要删除此内容吗？\n\n\t此操作将同时删除子项内容！且不能恢复，请慎用！");
	if(qc == "0")
	{
		return false;
	}
	var url = base_url+"del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "内容删除失败！";
		alert(msg);
		return false;
	}
}

//进入子项目
function son_opt(id)
{
	var url = base_url+"list&pid="+id;
	direct(url);
}