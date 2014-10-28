/***********************************************************
	Filename: {phpok}/js/plugin.js
	Note	: 插件中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 14:14
***********************************************************/

//配置插件
function plugin_config(id)
{
	var url = get_url("plugin","config") + "&id="+$.str.encode(id);
	direct(url);
}

//安装插件
function plugin_install(id)
{
	var url = get_url("plugin","install") + "&id="+$.str.encode(id);
	direct(url);
}

//卸载插件
function plugin_uninstall(id,title)
{
	var qc = confirm("确定要卸载插件："+title+" 吗？卸载后相应栏目不能使用！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("plugin","uninstall") + "&id="+$.str.encode(id);
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

//删除插件
function plugin_delete(id,title)
{
	var qc = confirm("确定要删除插件："+title+" 吗？删除后插件是不能恢复的，请慎用！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("plugin","delete") + "&id="+$.str.encode(id);
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