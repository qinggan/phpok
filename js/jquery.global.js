/**************************************************************************************************
	文件： js/jquery.global.js
	说明： PHPOK编写并整理的通用组件，包含常用的Ajax，弹出窗，桌面组件
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年7月11日
***************************************************************************************************/
;(function($){
	$.desktop = {
		init:function(opts){
			var defaults = {
				'win_min'		:true,
				'win_max'		:true,
				'win_close'		:true,
				'title'			:'',
				'iframe'		:'', // 定义iframe地址
				'content'		:'', // 定义内容信息，当内容为iframe时，调用iframe对应的url
				'lock'			:false,
				'bgcolor'		:'#fff',
				'position'		:'center center', //支持数字及常用单词center
				'taskbar'		:false,
				'width'			:'auto',
				'height'		:'auto',
				'prefix'		:'phpok-',
				'exheight'		:0,
				'exwidth'		:0,
				'func_close'	:false,
				'is_max'		:false, //是否直接全屏
				'move'			:true, //允许移动
				'z_index'		:1000, //弹出层的默认层级
				'close_tip'		:'' //关闭窗口前是否弹出提示
			};
			this.opt = $.extend({},defaults, opts);
			this.prefix = this.opt.prefix ? this.opt.prefix : 'phpok-';
		},
		_id:function(id)
		{
			if(!id || id == 'undefined'){
				var str = (Math.random()).toString();
				str = str.replace(".","");
				if(!this.prefix || this.prefix == 'undefined'){
					this.prefix = 'phpok-';
				}
				id = this.prefix + (str).toString();
			}
			this.id = id;
		},
		win_id: function(id)
		{
			this._id(id);
			return this.id;
		},
		win: function(opts)
		{
			this.opt = $.extend({},this.opt, opts);
			if(!this.id || this.id == 'undefined'){
				this._id();
			}
			this._style();
			this._content();
			this._pop();
			this._taskbar();
			this._action();
		},
		height: function(height)
		{
			var obj = this._win();
			if(height == 'auto' || !height || height == 'undefined' || parseInt(height,10) > obj.height){
				height = obj.height * 0.7;
			}
			if((new RegExp('%')).test(height)){
				height = parseInt((obj.height * parseInt(height,10))/100,10);
			}
			return height;
		},
		width: function(width)
		{
			var obj = this._win();
			if(width == 'auto' || !width || width == 'undefined' || parseInt(width,10) > obj.width){
				width = obj.width * 0.8;
			}
			if((new RegExp('%')).test(width)){
				width = parseInt((obj.width * parseInt(width,10))/100,10);
			}
			return width;
		},
		title: function(title,id)
		{
			if(!title || title == 'undefined'){
				return false;
			}
			if(!id || id == 'undefined'){
				var zindex = new Array();
				var idlist = new Array();
				var tmpi = 0;
				$("ul#"+this.prefix+"taskbar li").each(function(i){
					id = ($(this).attr('id')).substr(8);
					var ishidden = $("#"+id).is(":hidden");
					if(!ishidden){
						zindex[tmpi] = $("#"+id).css('z-index');
						idlist[tmpi] = id;
						tmpi++;
					}else{
						$(this).removeClass('on');
					}
				});
				//当
				if(zindex.length>0 && idlist.length>0){
					zmax = Math.max.apply(null,zindex);
					var t = 0;
					for(var i in zindex){
						if(zindex[i] == zmax){
							t = i;
						}
					}
					id = idlist[t];
				}
			}
			if(!id || id == 'undefined'){
				return false;
			}
			$("#title-"+id+",#taskbar-"+id).html(title);
		},
		_win:function(){
			var obj = {};
			obj.width = $(window).width() - parseInt(this.opt.exwidth,10);
			obj.height = $(window).height() - parseInt(this.opt.exheight,10);
			return obj;
		},
		_doc: function(){
			var obj = {};
			obj.width = $(document).width() - parseInt(this.opt.exwidth,10);
			obj.height = $(document).height() - parseInt(this.opt.exheight,10);
			return obj;
		},
		tohome:function(){
			$(".phpok-win").hide();
			$(".phpok-win-lock").hide();
			this._taskbar_on();
		},
		//取得指定ID的z-index值
		zindex:function(id){
			if(!id || id == 'undefined')
			{
				id = this.id;
			}
			var list = new Array();
			var total = $(".phpok-win").length;
			var zIndex = this.opt.z_index;
			if(total > 0)
			{
				$(".phpok-win").each(function(i){
					list[i] = $(this).css("z-index");
				});
				zIndex = Math.max.apply(null,list);
			}
			zIndex++;
			$("#"+id+"-lock").css("z-index",zIndex);
			$("#"+id).css("z-index",(zIndex+1));
			this._taskbar_on();
			return zIndex;
		},
		//触发当前高亮的taskbar
		_taskbar_on: function(){
			var id;
			var zindex = new Array();
			var idlist = new Array();
			var tmpi = 0;
			$("ul#"+this.prefix+"taskbar li").each(function(i){
				id = ($(this).attr('id')).substr(8);
				var ishidden = $("#"+id).is(":hidden");
				if(!ishidden)
				{
					zindex[tmpi] = $("#"+id).css('z-index');
					idlist[tmpi] = id;
					tmpi++;
				}
				$(this).removeClass('on');
			});
			//当
			if(zindex.length>0 && idlist.length>0)
			{
				zmax = Math.max.apply(null,zindex);
				var t = 0;
				for(var i in zindex)
				{
					if(zindex[i] == zmax)
					{
						t = i;
					}
				}
				id = idlist[t];
				$("#taskbar-"+id).addClass('on');
			}
		},
		//取得当前窗口中的最大值
		max_zindex: function(){
			var list = new Array();
			var total = $(".phpok-win").length;
			var zIndex = this.opt.z_index;
			if(total > 0)
			{
				$(".phpok-win").each(function(i){
					list[i] = $(this).css("z-index");
				});
				zIndex = Math.max.apply(null,list);
			}
			return zIndex;
		},
		close: function(id){
			if(this.opt.close_tip){
				var q = confirm(this.opt.close_tip);
				if(q == 0){
					return false;
				}
			}
			if(!id || id == 'undefined'){
				//获取ID
				var obj = $("div.phpok-win").length > 0 ? $("div.phpok-win") : parent.$("div.phpok-win");
				var zlist = new Array();
				var is_each = false;
				obj.each(function(i){
					zlist[i] = {'zindex':$(this).css('z-index'),'id':$(this).attr('id')};
				});
				if(zlist.length>0){
					var zmax = 0;
					for(var i in zlist){
						if(parseInt(zlist[i]['zindex'],10) > parseInt(zmax,10)){
							zmax = parseInt(zlist[i]['zindex'],10);
							id = zlist[i]['id'];
						}
					}
				}else{
					id = this.id;
				}
				if(id && id != 'undefined'){
					return obj.find("li#close-"+id).click();
				}
			}
			var arg = this._arg(id);
			if($("#"+id).length > 0){
				$("#"+id).remove();
				$("#"+id+"-lock").remove();
				$("#taskbar-"+id).remove();
				this._taskbar_on();
			}else{
				parent.$("div#"+id).remove();
				parent.$("iframe#"+id+"-lock").remove();
				parent.$("li#taskbar-"+id).remove();
			}
		},
		max: function(id){
			if(!id || id == 'undefined')
			{
				id = this.id;
			}
			var arg = this._arg(id);
			//判断当前窗口是否是最大值
			var w = $("#"+id).width();
			var is_max = w <= arg.width ? false : true;
			if(is_max)
			{
				//取消最大化
				$("#"+id).css({
					'width':arg.width+"px",
					'height':arg.height+'px',
					'left':arg.left+"px",
					'top':arg.top+'px'
				});
				$("#"+id+"-body").css({
					"width":parseInt((arg.width-12),10)+"px",
					'height':parseInt((arg.height-38),10)+"px"
				});
				if(arg.lock != 1)
				{
					$("#"+id+"-lock").css({
						'width':arg.width+"px",
						'height':arg.height+'px',
						'left':arg.left+"px",
						'top':arg.top+'px'
					});
				}
				$("#max-"+id).removeClass('max').addClass('max-max');
			}
			else
			{
				var win_height = $(window).height() - arg.exheight;
				if(arg.win_height>win_height)
				{
					arg.win_height = win_height;
				}
				$("#"+id).css({
					'width':arg.win_width+"px",
					'height':arg.win_height+'px',
					'left':arg.exwidth+"px",
					'top':arg.exheight+"px"
				});
				$("#"+id+"-body").css({
					"width":parseInt((arg.win_width-12),10)+"px",
					"height":parseInt((arg.win_height-38),10)+"px"
				});
				$("#max-"+id).removeClass('max-max').addClass('max');
				if(arg.lock != 1)
				{
					$("#"+id+"-lock").css({
						'width':arg.win_width+"px",
						'height':arg.win_height+'px',
						'left':arg.exwidth+"px",
						'top':arg.exheight+'px'
					});
				}
			}
			this._taskbar_on();
		},
		min: function(id){
			if(!id || id == 'undefined')
			{
				id = this.id;
			}
			$("#"+id+",#"+id+"-lock").hide();
			this._taskbar_on();
		},
		_arg: function(id){
			if(!id || id == 'undefined')
			{
				id = this.id;
			}
			var str = $("#"+id).attr('data');
			var arg = {};
			if(str)
			{
				t = str.split(';');
				for(var i in t)
				{
					var val = (t[i]).split(":");
					arg[val[0]] = val[1];
				}
			}
			return arg;
		},
		_style:function(){
			var width = (parseInt(this.width(this.opt.width),10)).toString();
			var height = (parseInt(this.height(this.opt.height),10)).toString();
			var win = this._win();
			var doc = this._doc();
			var zindex = this.zindex();
			//取得left，top位置坐标
			var position = this.opt.position ? (this.opt.position).toLowerCase() : 'center center';
			if(!position || position == 'center center' || position == 'center')
			{
				var left = parseInt((win.width - width)/2,10);
				var top = parseInt((win.height - height)/2,10);
			}
			else
			{
				var tlist = position.split(' ');
				var left = tlist[0] ? tlist[0] : 'center';
				var top = tlist[1] ? tlist[1] : 'center';
				if(left == 'center')
				{
					left = parseInt((win.width - width)/2,10);
				}
				if(top == 'center')
				{
					var top = parseInt((win.height - height)/2,10);
				}
			}
			this.style  = 'width:'+width+'px;';
			this.style += 'height:'+height+'px;';
			this.style += 'left:'+left+'px;';
			this.style += 'top:'+top+'px;';
			this.style += 'z-index:'+(zindex+1)+';';
			//锁屏样式
			this.lockstyle  = 'position:absolute;z-index:'+zindex+';';
			this.lockstyle += 'background:'+this.opt.bgcolor+';';
			if(this.opt.lock)
			{
				var tmpheight = doc.height > win.height ? doc.height : win.height;
				this.lockstyle += 'width:100%;';
				this.lockstyle += 'height:'+tmpheight+'px;';
				this.lockstyle += 'left:0;';
				this.lockstyle += 'top:0;';
			}
			else
			{
				this.lockstyle += 'width:'+width+'px;';
				this.lockstyle += 'height:'+height+'px;';
				this.lockstyle += 'left:'+left+'px;';
				this.lockstyle += 'top:'+top+'px;';
			}
			//内容高度样式
			this.bodystyle  = 'position:relative;';
			this.bodystyle += 'width:'+parseInt((width-12),10)+'px;';
			this.bodystyle += 'height:'+parseInt((height-38),10)+'px;';
			//记住当前窗口的宽高
			this.arg = 'width:'+width+";height:"+height+";win_width:"+win.width+";win_height:"+win.height;
			this.arg+= ";lock:"+(this.opt.lock ? '1' : '0');
			this.arg+= ";exheight:"+this.opt.exheight+";exwidth:"+this.opt.exwidth;
			this.arg+= ";left:"+left+";top:"+top;
			this.arg+= ";move:"+(this.opt.move ? '1' :'0');
			if(this.opt.taskbar)
			{
				this.arg+= ";taskbar:"+(this.opt.taskbar).toString();
			}
			
		},
		_save: function(arg,id)
		{
			var str = '';
			for(var i in arg)
			{
				str += i+":"+arg[i]+";";
			}
			var length = str.length;
			str = str.substr(0,length-1);
			$("#"+id).attr('data',str);
		},
		_content:function(){
			if((this.opt.content).substr(0,1) == '.' || (this.opt.content).substr(0,1) == '#')
			{
				//取得内容
				var div_content = $(this.opt.content).html();
				var content = '<div id="'+this.id+'-content" class="content">'+div_content+'</div>';
				//清空现有的标识内容
				$(this.opt.content).html('');
				//增加取消触发事件
				this.opt.func_cancel = function(){
					$(this.opt.content).html(div_content);
					$("#"+this.id+'-content').html('');
					return true;
				};
			}
			else if((this.opt.content == 'iframe' || !this.opt.content) && this.opt.iframe)
			{
				var url = this.opt.iframe;
				if(url.indexOf('_noCache') != -1)
				{
					url = url.replace(/\_noCache=[0-9\.]+/,'_noCache='+Math.random());
				}
				else
				{
					url += url.indexOf('?') != -1 ? '&' : '?';
					url += '_noCache='+Math.random();
				}
				var content = '<iframe src="'+url+'" border="0" frameborder="0" marginheight="0" marginwidth="0" class="content" style="overflow:hidden;height:100%;width:100%;background:#fff;" id="'+this.id+'-content"></iframe>';
			}
			else
			{
				var content = '<div id="'+this.id+'-content" class="content">'+this.opt.content+'</div>';
			}
			this.content = content;
		},
		_pop:function(){
			
			var html = '';
			html += '<iframe class="phpok-win-lock" marginheight="0" marginwidth="0" frameborder="0" scrolling="no" id="'+this.id+'-lock" style="'+this.lockstyle+'"></iframe>';
			html += '<div class="phpok-win" id="'+this.id+'" style="'+this.style+'" data="'+this.arg+'">';
			html += '<div class="window">';
			html += '	<div class="title">';
			html += '		<h3 class="h3" id="title-'+this.id+'">'+this.opt.title+'</h3>';
			html += '		<div class="button">';
			html += '		<ul>';
			if(this.opt.win_min){
				html += '		<li class="min" id="min-'+this.id+'"><a href="javascript:void(0);"></a></li>';
			}
			if(this.opt.win_max){
				html += '		<li class="max-max" id="max-'+this.id+'"><a href="javascript:void(0);"></a></li>';
			}
			if(this.opt.win_close){
				html += '		<li class="close" id="close-'+this.id+'"><a href="javascript:void(0);"></a></li>';
			}
			html += '		</ul>';
			html += '		</div>';
			html += '	</div>';
			html += '	<div class="body" id="'+this.id+'-body" style="'+this.bodystyle+'">';
			html += 	this.content;
			html += '	</div>';
			html += '</div>';
			$(html).appendTo("body");
		},
		_taskbar:function(id){
			if(!id || id == 'undefined')
			{
				id = this.id;
			}
			var arg = this._arg(id);
			if(arg.taskbar == 'false')
			{
				return true;
			}
			var ul_id = this.prefix+'taskbar';
			var html = '<li id="taskbar-'+id+'">'+this.opt.title+'</li>';
			$(html).appendTo("#"+ul_id);
			return true;
		},
		_vlayer: function(id){
			if(!id)
			{
				return false;
			}
			var width = $("#"+id).width();
			var height = $("#"+id).height();
			var left = parseInt($("#"+id).css('left'),10);
			var top = parseInt($("#"+id).css('top'),10);
			var css = "width:"+width+"px;height:"+height+"px;left:"+left+"px;top:"+top+'px;position:absolute;';
			var zindex = $("#"+id).css("z-index");
			css += "z-index:"+parseInt(zindex+1)+";";
			//css += "background:#FFF;"
			var html = '<div class="phpok-win-vlayer" id="vlayer-'+id+'" style="'+css+'">&nbsp;</div>';
			$(html).appendTo('body');
		},
		//关闭移动时的触发
		_vlayer_close: function(id){
			var arg = this._arg(id);
			var top = parseInt($("#vlayer-"+id).css("top"),10);
			var left = parseInt($("#vlayer-"+id).css('left'),10);
			$("#"+id).css({'top':top+"px",'left':left+"px"});//控件新位置
			if(arg.lock != 1)
			{
				$("#"+id+"-lock").css({'top':top+"px",'left':left+"px"});//控件新位置
			}
			arg.left = left;
			arg.top = top;
			this._save(arg,id);
			$("#vlayer-"+id).remove();
			return true;
		},
		//绑定相应的动作
		_action:function(){
			var _x,_y;//鼠标离控件左上角的相对位置
			var _move = false;
			var self = this;
			var id = this.id;
			var arg = this._arg(id);
			//存在关闭窗口执行的触发
			if(this.opt.win_close){
				$("#close-"+id).click(function(){
					if(self.opt.func_close){
						var obj = (self.opt.func_close)();
						if(!obj){
							return false;
						}else{
							self.close(id);
						}
					}else{
						self.close(id);
					}
				});
			}
			//有最大化最小化按钮时
			if(this.opt.win_max)
			{
				$("#title-"+id).dblclick(function(){self.max(id);});
				$("#max-"+id).click(function(){self.max(id);});
			}
			//有最小化时的按钮信息
			if(this.opt.win_min)
			{
				$("#min-"+id).click(function(){self.min(id);});
			}
			//如果有启用taskbar
			if(this.opt.taskbar)
			{
				$("#taskbar-"+id).click(function(){
					//判断是否最小化了
					var is_hidden = $("#"+id).is(":hidden");
					if(is_hidden)
					{
						$("#"+id).show();
						$("#"+id+"-lock").show();
						self.zindex(id);
					}
					else
					{
						var z_id = $("#"+id).css("z-index");
						var z_max = self.max_zindex();
						if(z_max != z_id)
						{
							self.zindex(id);
						}
						else
						{
							self.min(id);
						}
					}
					self._taskbar_on();
				});
			}
			//如果默认启用了is_max
			if(this.opt.is_max)
			{
				this.max(id);
			}
			//移动
			$("#title-"+id).mousedown(function(e){
				e = e || window.event;
				var zindex = self.zindex(id);
				//创建一个虚拟的DIV层
				_x = e.pageX-parseInt($("#"+id).css("left"));
				_y = e.pageY-parseInt($("#"+id).css("top"));
				if(arg.move == 1)
				{
					_move = true;
					self._vlayer(id);
				}
				e.preventDefault && e.preventDefault();
			}).mouseup(function(e){
				e = e || window.event;
				_move = false;
				self._vlayer_close(id);
				e.preventDefault && e.preventDefault();
			});
			$(document).mousemove(function(e){
				e = e || window.event;
				if(!_move)
				{
					return false;
				}
				var x = e.pageX - _x;
				var y = e.pageY - _y;
				if(self.opt.taskbar == 'top' && y < parseInt(self.opt.exheight,10))
				{
					y = parseInt(self.opt.exheight,10);
				}
				else
				{
					if(y<0)
					{
						y = 0;
					}
				}
				if(self.opt.taskbar == 'left' && x < parseInt(self.opt.exwidth,10))
				{
					x = parseInt(obj.opt.exwidth,10);
				}
				else
				{
					if(x < 0)
					{
						x = 0;
					}
				}
				var max_width = $(window).width();
				var max_height = $(window).height();
				var width = $("#"+id).width();
				var height = $("#"+id).height();
				if( (max_width - width) < x)
				{
					x = max_width - width;
				}

				if((max_height - height) < y)
				{
					y = max_height - height;
				}
				$("#vlayer-"+id).css({'top':y+"px",'left':x+"px"});//控件新位置
				e.preventDefault && e.preventDefault();
			}).mouseup(function(e){
				e = e || window.event;
				_move = false;
				self._vlayer_close(id);
				e.preventDefault && e.preventDefault();
			});
		}
	};
	$.win = function(title,url,opts){
		//检查是否窗口已存在
		var open_id = false;
		$("ul#phpok-taskbar li").each(function(i){
			var txt = $(this).text();
			if(txt == title){
				open_id = $(this).attr('id');
			}
		});
		if(open_id){
			$("#"+open_id).click();
			return false;
		}
		var max = $('body').width() - 770;
		var this_max = 90;
		$("ul.head_tab li").each(function(i){
			this_max += parseInt($(this).outerWidth(true));
		});
		if(max <= this_max)
		{
			$.dialog.alert('您弹出的窗口太多了，请先关闭几个没有用的窗口');
			return false;
		}
		var height = parseInt(($(window).height() - 45) * 0.8);
		var exwidth = $('.c_left').parent().width();
		if(!exwidth || exwidth == 'null' || exwidth == null){
			exwidth = 0;
		}
		var defaults = {
			'iframe':url,
			'title':title,
			'lock':false,
			'taskbar':'top',
			'exheight':45,
			'exwidth':exwidth,
			'height':height,
			'move':true,
			'win_max':true,
			'win_min':true,
			'is_max':true
		}
		if($.win2.opt){
			var opt = $.extend({},defaults,$.win2.opt, opts);
		}else{
			var opt = $.extend({},defaults, opts);
		}
		$.desktop.init(opt);
		$.desktop.win_id();
		$.desktop.win();
	};
	$.win2 = {
		init:function(opts){
			this.opt = opts;
		}
	};
})(jQuery);


(function($) {
	$.fn.wresize = function(f) {
		version = '1.1';
		wresize = {
			fired: false,
			width: 0
		};

		function resizeOnce() {
			if ($.browser.msie) {
				if (!wresize.fired) {
					wresize.fired = true;
				} else {
					var version = parseInt($.browser.version, 10);
					wresize.fired = false;
					if (version < 7) {
						return false;
					} else if (version == 7) {
						//a vertical resize is fired once, an horizontal resize twice 
						var width = $(window).width();
						if (width != wresize.width) {
							wresize.width = width;
							return false;
						}
					}
				}
			}
			return true;
		}

		function handleWResize(e) {
			if (resizeOnce()) {
				return f.apply(this, [e]);
			}
		}
		this.each(function() {
			if (this == window) {
				//alert("ttt");
				$(this).resize(handleWResize);
			} else {
				$(this).resize(f);
			}
		});
		return this;
	};
})(jQuery);