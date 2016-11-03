/***********************************************************
	Filename: js/global.js
	Note	: 后台通用JS，此JS应加载在jquery.js之后
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-19 16:58
***********************************************************/
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
	for(var i=0;i<lst_count;i++)
	{
		if($.inArray(lst[i],elist) < 0)
		{
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

function p_lang(str)
{
	if(!str || str == 'undefined'){
		return false;
	}
	if(lang && lang[str]){
		return lang[str];
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
	if(t == 'string') return id.length;
	if(t == 'object')
	{
		var n = 0;
		for(var i in id)
		{
			n++;
		}
		return n;
	}
	return false;
}

//JS语言包替换
function lang_replace(str,id,val)
{
	if(!str || str == "undefined") return false;
	if(!id || !val) return str;
	return str.replace("{"+id+"}",val);
}


