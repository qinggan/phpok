/**
 * 表单通用JS，涉及到自定义表单中所有的JS文件，请注意，此文件需要加载在 jQuery 之后，且不建议直接读取
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年03月22日
**/

//异步加载js
function load_js(url)
{
	if(!url || url == "undefined") return false;
	var lst = url.split(",");
	var lst_count = lst.length;
	var elist = new Array();
	var tm = 0;
	$("script").each(function(t){
		var src = $(this).attr("src");
		if(src && src != 'undefined'){
			elist[tm] = src;
			tm++;
		}
	});
	var html = '';
	for(var i=0;i<lst_count;i++){
		if($.inArray(lst[i],elist) < 0){
			html += '<script type="text/javascript" src="'+lst[i]+'"></script>';
		}
	}
	$("head").append(html);
}

// 同步加载Ajax，返回字符串
function get_ajax(turl)
{
	return $.phpok.ajax(turl);
}

// 同步加载Ajax，返回JSON数组
function json_ajax(turl)
{
	return $.phpok.json(turl);
}

/**
 * JS语法中涉及到的语言包替换
 * @参数 str 要替换的语言包，支持使用{}包起来的变量
 * @参数 info 支持字符串，对数数据，要替换的变量，为空表示没有变量信息
 * @返回 替换后的数据
 * @更新时间
**/
function p_lang(str,info)
{
	if(!str || str == 'undefined'){
		return false;
	}
	if(lang && lang[str]){
		if(!info || info == 'undefined' || typeof info == 'boolean'){
			return lang[str];
		}
		str = lang[str];
		if(typeof info == 'string' || typeof info == 'number'){
			return str.replace(/(\{|\[)\w+?(\}|\])/,info);
		}
		for(var i in info){
			str = str.replace('{'+i+'}',info[i]);
			str = str.replace('['+i+']',info[i]);
		}
		return str;
	}
	if(!info || info == 'undefined' || typeof info == 'boolean'){
		return str;
	}
	if(typeof info == 'string' || typeof info == 'number'){
		return str.replace(/(\{|\[)\w+?(\}|\])/,info);
		//return str.replace(/\{\w+\}/,info);
	}
	for(var i in info){
		str = str.replace('{'+i+'}',info[i]);
		str = str.replace('['+i+']',info[i]);
	}
	return str;
}

// 异步加载Ajax，执行函数
function ajax_async(turl,func,type)
{
	if(!turl || turl == "undefined")
	{
		return false;
	}
	if(!func || func == "undefined")
	{
		return false;
	}
	if(!type || type == "undefined")
	{
		type = "json";
	}
	if(type != "html" && type != "json" && type != "text" && type != "xml")
	{
		type = "json";
	}
	turl = $.phpok.nocache(turl);
	$.ajax({
		'url': turl,
		'cache': false,
		'async': true,
		'dataType': type,
		'success': function(rs){
			(func)(rs);
		}
	});
}

// 跳转页面
function direct(url)
{
	if(!url || url == "undefined") url = window.location.href;
	$.phpok.go(url);
}

//自动刷新
function auto_refresh(rs)
{
	$.phpok.reload();
}

function autosave_callback(rs)
{
	return true;
}

/* 计算字符数长度，中文等同于三个字符，英文为一个字符 */
function strlen(str)
{
	var len = str.length;
	var reLen = 0;
	for (var i = 0; i < len; i++)
	{
		if (str.charCodeAt(i) < 27 || str.charCodeAt(i) > 126)
		{
			reLen += 3;
		} else {
			reLen++;
		}
	}
	if(reLen > 1024 && reLen < (1024 * 1024))
	{
		var reLen = (parseFloat(reLen / 1024).toFixed(3)).toString() + "KB";
	}
	else if(reLen > (1024 * 1024) && reLen < (1024 * 1024 * 1024))
	{
		var reLen = (parseFloat(reLen / (1024 * 1024)).toFixed(3)).toString() + "MB";
	}
	if(!reLen) reLen = "0";
	return reLen;
}


//友情提示
function tips(content,time,id)
{
	if(!time || time == "undefined") time = 1.5;
	if(!id || id == "undefind")
	{
		$.dialog.tips(content,time);
	}
	else
	{
		return $.dialog({
			id: 'Tips',
			title: false,
			cancel: false,
			fixed: true,
			lock: false,
			focus: id,
			resize: false
		}).content(content).time(time || 1.5);
	}
}

/* 计算数组或对像中的个数 */
function count(id)
{
	var t = typeof id;
	if(t == 'string'){
		return id.length;
	}
	if(t == 'object'){
		var n = 0;
		for(var i in id){
			n++;
		}
		return n;
	}
	return false;
}
