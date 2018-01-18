/**
 * JS初始化库
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年12月01日
**/
var basefile = "{$basefile}";
var ctrl_id = "{$sys.ctrl_id}";
var func_id = "{$sys.func_id}";
var webroot = "{$sys.url}";
var apifile = "{$sys.api_file}";
var lang = new Array();
<!-- loop from=$langs key=$key value=$value id=$tmpid -->
lang["{$key}"] = "{$value}";
<!-- /loop -->

function get_url(ctrl,func,ext)
{
	var url = webroot+""+basefile+"?"+ctrl_id+"="+ctrl;
	if(func){
		url += "&"+func_id+"="+func;
	}
	if(ext){
		url += "&"+ext;
	}
	return url;
}

function get_plugin_url(id,efunc,ext)
{
	var url = webroot+""+basefile+"?"+ctrl_id+"=plugin&"+func_id+"=exec";
	if(id){
		url += "&id="+id;
	}
	if(efunc){
		url += "&exec="+efunc;
	}
	if(ext){
		url += "&"+ext;
	}
	return url;
}

function api_url(ctrl,func,ext)
{
	var url = webroot+""+apifile+"?"+ctrl_id+"="+ctrl;
	if(func){
		url += "&"+func_id+"="+func;
	}
	if(ext){
		url += "&"+ext;
	}
	return url;
}


function api_plugin_url(id,efunc,ext)
{
	var url = webroot+""+apifile+"?"+ctrl_id+"=plugin&"+func_id+"=exec";
	if(id){
		url += "&id="+id;
	}
	if(efunc){
		url += "&exec="+efunc;
	}
	if(ext){
		url += "&"+ext;
	}
	return url;
}

{$jquery}