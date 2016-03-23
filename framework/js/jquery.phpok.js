// PHPOK程序中常用到的JS，封装在此
// 水平比较菜:)
;(function($){
	$.phpok = {
		//刷新
		refresh: function(){
			var url = window.location.href;
			url = this.nocache(url);
			this.go(url);
		},
		reload:function(){
			this.refresh();
		},
		go: function(url,ext){
			if(!url){
				return false;
			}
			if(ext || ext == 'undefined'){
				url = this.nocache(url);
			}			
			window.location.href = url;
		},
		ajax:function(url,obj,async){
			if(!url){
				return false;
			}
			url = this.nocache(url);
			if(!obj || obj == 'undefined'){
				return $.ajax({'url':url,cache:false,async:false,dataType:"html"}).responseText;
			}else{
				async = (!async || async == 'undefined') ? false : true;
				$.ajax({
					'url':url,
					'cache':false,
					'async':async,
					'dataType':'html',
					'success':function(rs){
						(obj)(rs);
					}
				});
			}
		},
		json:function(url,obj,async){
			if(!url){
				return false;
			}
			url = this.nocache(url);
			if(!obj || obj == 'undefined'){
				var info = $.ajax({'url':url,cache:false,async:false,dataType:"html"}).responseText;
				return $.parseJSON(info);
			}else{
				async = (!async || async == 'undefined') ? false : true;
				$.ajax({
					'url':url,
					'cache':false,
					'async':async,
					'dataType':'json',
					'success':function(rs){
						(obj)(rs);
					}
				});
			}
		},
		nocache: function(url){
			url = url.replace(/&amp;/g,'&');
			if(url.indexOf('_noCache') != -1){
				url = url.replace(/\_noCache=[0-9\.]+/,'_noCache='+Math.random());
			}else{
				url += url.indexOf('?') != -1 ? '&' : '?';
				url += '_noCache='+Math.random();
			}
			return url;
		}
	};

})(jQuery);

//JS操作全选，反选等工具
// 由PHPOK整理重新编写的常见的input属性操作
;(function($){

	$.input = {
		obj: function(id){
			if(id && id != 'undefined'){
				if(id.match(/^[a-zA-Z0-9\-\_]{1,}$/)){
					id = '#'+id+" input[type=checkbox]";
				}
				var t = $(id);
			}else{
				var t = $('input[type=checkbox]');
			}
			return t;
		},
		//全选，调用方法：$.input.checkbox_all(id);
		checkbox_all: function(id){
			var t = this.obj(id);
			t.each(function(){$(this).attr("checked",true);});
			t = null;
		},
		//全不选，调用方法：$.input.checkbox_none(id);
		checkbox_none: function(id){
			var t = this.obj(id);
			t.each(function(){$(this).attr("checked",false);});
			t = null;
		},
		//每次选5个（total默认值为5） $.input.checkbox_not_all(id,5);
		checkbox_not_all: function(id,total){
			var t = this.obj(id);
			var num = 0;
			if(!total || parseInt(total)<5) total = 5;
			t.each(function(){
				if($(this).attr("checked") != true && num<total)
				{
					$(this).attr("checked",true);
					num++;
				}
			});
			t = num = total = null;
		},
		//反选，调用方法：$.input.checkbox_anti(id);
		checkbox_anti: function(id){
			var t = this.obj(id);
			t.each(function(i){
				if($(this).attr("checked") == true || $(this).attr("checked") == "checked"){
					$(this).attr("checked",false);
				}else{
					$(this).attr("checked",true);
				}
			});
			t = null;
		},

		//合并复选框值信息，以英文逗号隔开
		checkbox_join: function(id,type){
			var cv = this.obj(id);
			var idarray = new Array();
			var m = 0;
			cv.each(function()
			{
				if(type == "all"){
					idarray[m] = $(this).val();
					m++;
				}else if(type == "unchecked"){
					if($(this).attr("checked") == false){
						idarray[m] = $(this).val();
						m++;
					}
				}else{
					if($(this).attr("checked") == true || $(this).attr("checked") == "checked"){
						idarray[m] = $(this).val();
						m++;
					}
				}
			});
			var tid = idarray.join(",");
			cv = idarray = m = null;
			return tid;
		}

	};

})(jQuery);

/*!
 * http://www.phpok.com/
 *
 * Copyright 2011, phpok.com
 * Released under the MIT, BSD, and LGPL Licenses.
 * 字符串编码，使用方法： $.str.encode(string);
 * 字符串合并，使用方法： $.str.join(str1,str2);
 *
 * Date: 2011-12-01 11:47
 */
;(function($){

	$.str = {
		join: function(str1,str2){
			if(str1 == "" && str2 == "" ) return false;
			if(str1 == "") return str2;
			if(str2 == "") return str1;
			var string = str1 + "," +str2;
			var array = string.split(",");
			array = $.unique(array);
			var string = array.join(",");
			return string ? string : false;
		},
		identifier: function(str){
			//验证标识串，PHPOK系统中，大量使用标识串，将此检测合并进来
			var chk = /^[A-Za-z]+[a-zA-Z0-9_\-]*$/;
			return chk.test(str);
		},
		encode: function(s1){
			return encodeURIComponent(s1);
		}
	};

	$.identifier = function(str){
		return $.str.identifier(str);
	};
})(jQuery);


function identifier(str)
{
	return $.str.identifier(str);
}

// 由PHPOK编写的基于jQuery的Cookie操作
// 读取cookie信息 $.cookie.get("变量名");
// 设置cookie信息 $.cookie.set("变量名","值","过期时间");
// 删除Cookie信息 $.cookie.del("变量名");

;(function($){
	$.cookie = {
		get: function(name) {
			var cookieValue = "";
			var search = name + "=";
			if(document.cookie.length > 0)
			{
				var offset = document.cookie.indexOf(search);
				if (offset != -1)
				{
					offset += search.length;
					var end = document.cookie.indexOf(";", offset);
					if (end == -1)
					{
						end = document.cookie.length;
					}
					cookieValue = unescape(document.cookie.substring(offset, end));
					end = null;
				}
				search = offset = null;
			}
			return cookieValue;
		},
		set: function(cookieName,cookieValue,DayValue){
			var expire = "";
			var day_value=1;
			if(DayValue!=null)
			{
				day_value=DayValue;
			}
			expire = new Date((new Date()).getTime() + day_value * 86400000);
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape(cookieValue) +";path=/"+ expire;
			cookieName = cookieValue = DayValue = day_value = expire = null;
		},
		del: function(cookieName){
			var expire = "";
			expire = new Date((new Date()).getTime() - 1 );
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape("") +";path=/"+ expire;		
			cookieName = expire = null;
		}
	};
})(jQuery);

