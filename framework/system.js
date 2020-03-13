/**
 * JS初始化库
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年12月01日
**/

/**
 * 常规基础变量，也是系统用到的变量
 */
var basefile = "{$basefile}";
var ctrl_id = "{$sys.ctrl_id}";
var func_id = "{$sys.func_id}";
var webroot = "{$sys.url}";
var apifile = "{$sys.api_file}";
var wwwfile = "{$sys.www_file}";
<!-- if APP_ID == 'admin' -->
var adminfile = "{$sys.admin_file}";
<!-- /if -->
var phpok_site_id = "{$phpok_site_id}";
var site_id = "{$site_id}";
var session_name = '{func session_name}';
var session_id = '{func session_id}';

//是否启用电调
var biz_status = '{$config.biz_status}';


/**
 * 常规基础变量结束
 */


/**
 * 加载语言包
 */
var lang = new Array();

<!-- loop from=$langs key=$key value=$value id=$tmpid -->
lang["{$key}"] = "{$value}";
<!-- /loop -->

/**
 * 结束语言包
 */

/**
 * 开始加载Jquery，注意，系统会尝式智能检测加载的jquery版本
 */

{$jquery}

/**
 * 结束加载Jquery
 */

;(function($){
	$.phpokurl = {
		base:function(ctrl,func,ext,file)
		{
			var url = webroot + "" +file;
			var is_wen = true;
			if(ctrl && ctrl != 'index'){
				url += "?"+ctrl_id+"="+ctrl;
				is_wen = false;
			}
			if(func && func != 'index'){
				if(is_wen){
					url += "?";
					is_wen = false;
				}else{
					url += "&";
				}
				url += func_id+"="+func;
			}
			if(ext){
				url += is_wen ? ("?"+ext) : ("&"+ext);
			}
			if(phpok_site_id && site_id && phpok_site_id != site_id){
				url += is_wen ? ("?siteId="+phpok_site_id) : ("&siteId="+phpok_site_id);
			}
			return url;
		},
		plugin:function(id,efunc,ext,file)
		{
			var url = webroot+""+file+"?"+ctrl_id+"=plugin&"+func_id+"=exec";
			if(id){
				url += "&_phpokid="+id;
			}
			if(efunc){
				url += "&exec="+efunc;
			}
			if(ext){
				url += "&"+ext;
			}
			if(phpok_site_id && site_id && phpok_site_id != site_id){
				url += "&siteId="+phpok_site_id;
			}
			return url;
		}
	}
})(jQuery);

function get_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,basefile);
}

function get_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,basefile);
}

function admin_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,adminfile);
}

function admin_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,adminfile);
}

function www_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,wwwfile);
}

function www_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,wwwfile);
}

function api_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,apifile);
}

function api_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,apifile);
}
