/***********************************************************
	Filename: {phpok}/js/all.js
	Note	: 全局模块参数设置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-19 20:20
***********************************************************/
//检查网站配置域名是否符合要求
function all_setting_check()
{
	var title = $("#title").val();
	if(!title)
	{
		alert("网站名称不能为空！");
		return false;
	}
	//检测域名
	var domain_check = _domain_check($("#domain").val());
	if(!domain_check)
	{
		return false;
	}
	return true;
}

function _domain_check(domain,isadd,domain_id)
{
	if(!domain)
	{
		alert("域名不能为空！");
		return false;
	}
	domain = domain.toLowerCase();
	if(domain.substr(0,7) == "http://" || domain.substr(0,8) == "https://")
	{
		alert("域名不能以http://或https://开头！");
		return false;
	}
	var chk = new RegExp('/');
	if(chk.test(domain))
	{
		alert("域名不能含有字符 / ");
		return false;
	}
	//检测此域名是否被使用
	var url = get_url("all","domain_check") + "&domain="+$.str.encode(domain);
	if(isadd && isadd != "undefined")
	{
		url += "&isadd=1";
	}
	if(domain_id && domain_id != "undefined")
	{
		url += "&id="+domain_id;
	}
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		alert(rs.content);
		return false;
	}
	return true;
}

//添加域名
function domain_add()
{
	var domain = $("#domain_0").val();
	var domain_check = _domain_check(domain,1);
	if(!domain_check)
	{
		return false;
	}
	var url = get_url("all","domain_save")+"&domain="+$.str.encode(domain);
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		alert("域名添加成功！");
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//更新域名
function domain_update(id)
{
	var domain = $("#domain_"+id).val();
	var domain_check = _domain_check(domain,0,id);
	if(!domain_check)
	{
		return false;
	}
	var url = get_url("all","domain_save")+"&domain="+$.str.encode(domain);
	url += "&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		alert("域名更新成功！");
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//设置主域名
function domain_default(id)
{
	var url = get_url("all","domain_default")+"&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//删除域名
function domain_delete(id)
{
	var qc = confirm("确定要删除此域名吗？");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("all","domain_delete")+"&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//添加或修改扩展组ID
function g_ext_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(!title)
	{
		alert("名称不能为空！");
		return false;
	}
	var identifier = $("#identifier").val();
	if(!identifier)
	{
		alert("标识串不能为空");
		return false;
	}
	var chk = $.str.identifier(identifier);
	if(!chk)
	{
		alert("标识串不符合条件要求！");
		return false;
	}
	//检测是否被使用了
	identifier = identifier.toLowerCase();
	if(identifier == "config" || identifier == "phpok")
	{
		alert("config 和 phpok 是系统变量，不允许使用");
		return false;
	}
	//检测字串是否被使用了
	var url = get_url("all","all_check")+"&identifier="+$.str.encode(identifier);
	if(id)
	{
		url +="&id="+id;
	}
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		alert(rs.content);
		return false;
	}
	var ico = $("input[name=ico]").val();
	if(!ico)
	{
		alert("请选择一个图标！");
		return false;
	}
	return true;
}

//添加字段
function all_add_ext(id,t)
{
	var url = get_url("all","ext_add") + "&id="+id;
	var all_id = $("#id").val();
	if(!all_id)
	{
		alert("添加异常，未指定ID！");
		return false;
	}
	url += '&all_id='+all_id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		//自动保存表单
		autosave("ext_post","all",auto_refresh);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//删除扩展字段
function all_ext_delete(id,title)
{
	var qc = confirm("确定要删除扩展字段："+title+" 吗？删除后是不能恢复的！");
	if(qc == "0")
	{
		return false;
	}
	var cate_id = $("#id").val();
	url = get_url("all","ext_delete");
	if(!cate_id)
	{
		alert("未指定全局ID！");
		return false;
	}
	url += "&all_id="+cate_id;
	url += "&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		autosave("ext_post","all",auto_refresh);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

//删除扩展组信息
function ext_g_delete(id)
{
	var qc = confirm("确定要删除此组信息吗？删除后相关数据都会一起被删除！\n\n如果您有插件使用了这些数据，可能会造成插件不能正常运行！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("all","ext_gdelete")+"&id="+id;
	direct(url);
}


function email_setting(val)
{
	if(val == 1)
	{
		$("#email_setting").show();
	}
	else
	{
		$("#email_setting").hide();
	}
}

//更新URL引挈规则
//实现伪静态页方案
function set_url_type(val)
{
	if(!val || val == 'undefined')
	{
		val = 'default';
	}
	$("#url_type_default,#url_type_rewrite,#url_type_html").hide();
	$("#url_type_"+val).show();
}