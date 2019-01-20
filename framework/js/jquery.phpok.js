/**
 * PHPOK程序中常用到的JS，封装在此
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @日期 2017年04月18日
**/

;(function($){
	$.phpok = {

		/**
		 * 刷新当前页面，使用方法：$.phpok.refresh();
		**/
		refresh: function()
		{
			window.location.reload(true);
		},

		/**
		 * 刷新页面别名，使用方法：$.phpok.reload();
		**/
		reload:function()
		{
			this.refresh();
		},

		/**
		 * 跳转到目标网址
		 * @参数 url 要跳转到的网址
		 * @参数 nocache 是否禁止缓存，设置且为true时，程序会在网址后面补增_noCache参数
		**/
		go: function(url,nocache)
		{
			if(!url){
				return false;
			}
			if(nocache || nocache == 'undefined'){
				url = this.nocache(url);
			}
			window.location.href = url;
		},

		/**
		 * 弹出窗口
		 * @参数 url 要弹出窗口的网址
		 * @参数 nocache 是否禁止缓存，设置且为true时，程序会在网址后面补增_noCache参数
		**/
		open:function(url,nocache)
		{
			if(!url){
				return false;
			}
			if(nocache || nocache == 'undefined'){
				url = this.nocache(url);
			}
			window.open(url);
		},

		/**
		 * 读取Ajax的内容，读出来的内容为html
		 * @参数 url 目标网址
		 * @参数 obj 执行方法，为空或未设置，则返回HTML代码，此时为同步请求
		**/
		ajax:function(url,obj,postData)
		{
			if(!url){
				return false;
			}
			var cls = {'url':url,'cache':false,'dataType':'html'};
			if(postData && postData != 'undefined'){
				cls.data = postData;
				cls.type = 'post';
			}
			cls.beforeSend = function(request){
				request.setRequestHeader("request_type","ajax");
				request.setRequestHeader("phpok_ajax",1);
				if(session_name && session_name != 'undefined'){
					request.setRequestHeader(session_name,$.cookie.get(session_name));
				}
			};
			if(!obj || obj == 'undefined'){
				cls.async = false;
				return $.ajax(cls).responseText;
			}
			cls.success = function(rs){(obj)(rs)};
			$.ajax(cls);
		},

		/**
		 * 读取 Ajax 内容，返回JSON数据
		 * @参数 url 目标网址
		 * @参数 obj 执行方法，为空或未设置，则返回JSON对象，此时为同步请求
		**/
		json:function(url,obj,postData)
		{
			if(!url){
				return false;
			}
			var self = this;
			var cls = {'url':url,'cache':false,'dataType':'json'};
			if(postData && postData != 'undefined'){
				cls.data = postData;
				cls.type = 'post';
			}
			cls.beforeSend = function(request){
				request.setRequestHeader("request_type","ajax");
				request.setRequestHeader("phpok_ajax",1);
				if(!postData || postData == 'undefined'){
					request.setRequestHeader("content-type","application/json");
				}
				if(session_name && session_name != 'undefined'){
					request.setRequestHeader(session_name,$.cookie.get(session_name));
				}
			};
			if(!obj || obj == 'undefined'){
				cls.async = false;
				var info = $.ajax(cls).responseText;
				return self.json_decode(info);
			}
			if(typeof obj == 'boolean'){
				cls.success = function(rs){
					return true;
				}
			}else{
				cls.success = function(rs){
					(obj)(rs);
				};
			}
			$.ajax(cls);
		},

		/**
		 * 格式化网址，增加_noCache尾巴，以保证不从缓存中读取数据
		 * @参数 url 要格式化的网址
		**/
		nocache: function(url)
		{
			url = url.replace(/&amp;/g,'&');
			if(url.indexOf('_noCache') != -1){
				url = url.replace(/\_noCache=[0-9\.]+/,'_noCache='+Math.random());
			}else{
				url += url.indexOf('?') != -1 ? '&' : '?';
				url += '_noCache='+Math.random();
			}
			return url;
		},


		json_encode:function(obj)
		{
			if(!obj || obj == 'undefined'){
				return false;
			}
			return JSON.stringify(obj);
		},


		json_decode:function(str)
		{
			if(!str || str == 'undefined'){
				return false;
			}
			return $.parseJSON(str);
		},

		/**
		 * 生成随机数
		 * @参数 len 长度，留空使用长度10
		 * @参数 type 类型，支持 letter,num,fixed,all，其中 fixed 表示字母数字混合，all 表示字母，数字，及特殊符号，letter 表示字母，num 表示数字
		**/
		rand:function(len,type)
		{
			len = len || 10;
			if(!type || type == 'undefined'){
				type = 'letter';
			}
			var types = {'letter':'abcdefhijkmnprstwxyz','num':'0123456789','fixed':'abcdefhijkmnprstwxyz0123456789','all':'abcdefhijkmnprstwxyz0123456789-,.*!@#$%=~'}
			if(type != 'letter' && type != 'num' && type != 'all' && type != 'fixed'){
				type = 'letter';
			}
			var string = types[type];
			var length = string.length;
			var val = '';
			for (i = 0; i < len; i++) {
				val += string.charAt(Math.floor(Math.random() * length));
			}
			return val;
		},
		/**
		 * 向顶层发送消息
		 * @参数 info 要发送的文本消息，注意，仅限文本
		**/
		message:function(info,url)
		{
			try{
				if(url && url != 'undefined'){

					$("iframe").each(function(i){
						var src = $(this).attr('src');
						if(typeof url == 'boolean'){
							var obj = $(this)[0].contentWindow;
							obj.postMessage(info,window.location.origin);
						}else{
							if(url.indexOf(src) != -1){
								var obj = $(this)[0].contentWindow;
								obj.postMessage(info,url)
							}
						}
					});
				}else{
					window.top.postMessage(info,top.window.location.origin);
				}
			} catch (error) {
				console.log(error);
				return false;
			}
		},
		data:function(id,val)
		{
			if(val && val != 'undefined'){
				localStorage.setItem(id,val);
				return true;
			}
			var info = localStorage.getItem(id);
			if(!info || info == 'undefined'){
				return false;
			}
			return info;
		},
		undata:function(id)
		{
			localStorage.removeItem(id);
		}
	};

	/**
	 * JSON字串与对象转换操作
	**/
	$.json = {

		/**
		 * 字符串转对象
		 * @参数 str 要转化的字符串
		**/
		decode:function(str)
		{
			if(!str || str == 'undefined'){
				return false;
			}
			return JSON.parse(str);
		},

		/**
		 * 对象转成字符串
		 * @参数 obj 要转化的对象
		**/
		encode:function(obj)
		{
			if(!obj || obj == 'undefined'){
				return false;
			}
			return JSON.stringify(obj);
		}
	};

	$.checkbox = {
		_obj:function(id)
		{
			if(id && id != 'undefined' && typeof id == 'string'){
				if(id.match(/^[a-zA-Z0-9\-\_]{1,}$/)){
					if($("#"+id).is('input')){
						return $("#"+id);
					}
					return $("#"+id+" input[type=checkbox]");
				}
				if($(id).is('input')){
					return $(id);
				}
				return $(id+" input[type=checkbox]");
			}
			return $("input[type=checkbox]");
		},

		/**
		 * 全选
		 * @参数 id 要操作的ID
		**/
		all:function(id)
		{
			var obj = this._obj(id);
			obj.prop('checked',true);
            window.setTimeout("layui.form.render('checkbox')",100);
			return true;
		},

		/**
		 * 返先
		 * @参数 id 要操作的ID
		**/
		none:function(id)
		{
			var obj = this._obj(id);
			obj.removeAttr('checked');
            window.setTimeout("layui.form.render('checkbox')",100);
			return true;
		},

		/**
		 * 更多选择，默认只选5个（count默认值为5） $.checkbox.more(id,5);
		 * @参数 id 要操作的ID
		 * @参数 count 每次次最多选几个
		**/
		more: function(id,count){
			var obj = this._obj(id);
			var num = 0;
			if(!count || count == 'undefined' || parseInt(count)<5){
				count = 5;
			}
			obj.each(function(){
				if(!$(this).is(":checked") && num<count){
					$(this).prop("checked",true);
					num++;
				}
			});
            window.setTimeout("layui.form.render('checkbox')",100)
			return true;
		},

		/**
		 * 反选，调用方法：$.checkbox.anti(id);
		 * @参数 id 要操作的ID
		**/
		anti:function(id)
		{
			var t = this._obj(id);
			t.each(function(i){
				if($(this).is(":checked")){
					$(this).removeAttr('checked');
				}else{
					$(this).prop('checked',true);
				}
				window.setTimeout("layui.form.render('checkbox')",100)
			});
		},

		/**
		 * 合并复选框值信息
		 * @参数 id 要操作的ID
		 * @参数 type 要支持合关的字符
		 * @参数 str 要连接的字符，为空或未设置使用英文逗号隔开
		**/
		join:function(id,type,str)
		{
			var cv = this._obj(id);
			var idarray = new Array();
			var m = 0;
			cv.each(function(){
				if(type == "all"){
					idarray[m] = $(this).val();
					m++;
				}else if(type == "unchecked" && !$(this).is(':checked')){
					idarray[m] = $(this).val();
					m++;
				}else{
					if($(this).is(':checked')){
						idarray[m] = $(this).val();
						m++;
					}
				}
			});
			var linkid = (str && str != 'undefined') ? str : ',';
			var tid = idarray.join(linkid);
			return tid;
		}
	}

	/**
	 * 字符串相关操作
	**/
	$.str = {

		/**
		 * 字符串合并，用英文逗号隔开
		 * @参数 str1 要合并的字符串1
		 * @参数 str2 要合并的字符串2
		**/
		join: function(str1,str2){
			var string = '';
			if(!str1 || str1 == 'undefined'){
				if(!str2 || str2 == 'undefined'){
					return false;
				}
				string = str2;
			}
			if(str1 && str1 != 'undefined'){
				if(!str2 || str2 == 'undefined'){
					string = str1;
				}else{
					string = str1 + "," + str2;
				}
			}
			if(string == ''){
				return false;
			}
			var array = string.split(",");
			array = $.unique(array);
			string = array.join(",");
			return string ? string : false;
		},

		/**
		 * 字符串标识符检测
		 * @参数 str 要检测的字符串
		 * @返回 true 或 false
		**/
		identifier: function(str){
			//验证标识串，PHPOK系统中，大量使用标识串，将此检测合并进来
			var chk = /^[A-Za-z]+[a-zA-Z0-9_\-]*$/;
			return chk.test(str);
		},

		/**
		 * 网址常规编码
		 * @参数 str 要编码的字符串
		**/
		encode: function(str){
			return encodeURIComponent(str);
		}
	};

	/**
	 * 由PHPOK编写的基于jQuery的Cookie操作
	 * 读取cookie信息 $.cookie.get("变量名");
	 * 设置cookie信息
	 * 删除Cookie信息 $.cookie.del("变量名");
	**/
	$.cookie = {

		/**
		 * 取得 Cookie 信息 $.cookie.get('变量名')
		 * @参数 name 要获取的 cookie 变量中的标识
		**/
		get: function(name)
		{
			var cookieValue = "";
			var search = name + "=";
			if(document.cookie.length > 0){
				var offset = document.cookie.indexOf(search);
				if (offset != -1){
					offset += search.length;
					var end = document.cookie.indexOf(";", offset);
					if (end == -1){
						end = document.cookie.length;
					}
					cookieValue = unescape(document.cookie.substring(offset, end));
					end = null;
				}
				search = offset = null;
			}
			return cookieValue;
		},

		/**
		 * 设置 Cookie 信息 $.cookie.set("变量名","值","过期时间");
		 * @参数 cookieName 变量名
		 * @参数 cookieValue 变量内容
		 * @参数 DayValue 过期时间，默认是1天，单位是天
		 * @返回
		 * @更新时间
		**/
		set: function(cookieName,cookieValue,DayValue)
		{
			var expire = "";
			var day_value=1;
			if(DayValue!=null){
				day_value=DayValue;
			}
			expire = new Date((new Date()).getTime() + day_value * 86400000);
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape(cookieValue) +";path=/"+ expire;
			cookieName = cookieValue = DayValue = day_value = expire = null;
		},

		/**
		 * 删除 Cookie 操作
		 * @参数 cookieName 变量名
		**/
		del: function(cookieName){
			var expire = "";
			expire = new Date((new Date()).getTime() - 1 );
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape("") +";path=/"+ expire;
			cookieName = expire = null;
		}
	};

	$.extend({
		identifier:function(id)
		{
			return $.str.identifier(id);
		}
	});

})(jQuery);

function identifier(str)
{
	return $.str.identifier(str);
}


/**
 * 旧版 Input 操作类
**/
;(function($){

	$.input = {

		checkbox_all: function(id)
		{
			return $.checkbox.all(id);
		},

		//全不选，调用方法：$.input.checkbox_none(id);
		checkbox_none: function(id)
		{
			return $.checkbox.none(id);
		},

		//每次选5个（total默认值为5） $.input.checkbox_not_all(id,5);
		checkbox_not_all: function(id,total)
		{
			return $.checkbox.more(id,total);
		},

		//反选，调用方法：$.input.checkbox_anti(id);
		checkbox_anti: function(id)
		{
			return $.checkbox.anti(id);
		},

		//合并复选框值信息，以英文逗号隔开
		checkbox_join: function(id,type)
		{
			return $.checkbox.join(id,type);
		}

	};

})(jQuery);