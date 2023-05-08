/**
 * 个人中心首页
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年3月16日
**/

;(function($){
	$.win = function(title,url,opts){
		setTimeout(function(){
			top.layui.index.openTabsPage(url, title);
		}, 500);
		return true;
	};
})(jQuery);

//后台常用JS函数封装
;(function($){
	$.admin = {
		//更换Tab设置
		tab:function(val)
		{
			$("#float_tab li").each(function(i){
				var name = $(this).attr("name");
				if(name == val)
				{
					$(this).removeClass("tab_out").addClass("tab_over");
					$("#"+val+"_setting").show();
				}
				else
				{
					$(this).removeClass("tab_over").addClass("tab_out");
					$("#"+name+"_setting").hide();
				}
			});
		},
		group:function(obj)
		{
			var val = $(obj).attr('name');
			$.each($(obj).parent().find('li'),function(i){
				var name = $(this).attr('name');
				$(this).removeClass('on');
				$("#"+name+"_setting").hide();
			});
			//显示当前的
			$(obj).addClass('on');
			$("#"+val+"_setting").show();
		},
		//基于父级窗口执行的badge，具体查看 admin.index.js
		badge:function()
		{
			$("em.toptip").remove();
			var badge = $.cookie.get('badge');
			if(badge){
				var list = badge.split(",");
				for(var i in list){
					var tmp = (list[i]).split(":");
					$("li[pid="+tmp[0]+"] a").append('<em class="toptip">'+tmp[1]+'</em>');
					$("li[id=project_"+tmp[0]+"]").append('<em class="toptip">'+tmp[1]+'</em>');
				}
				return true;
			}
			return true;
		},
		//搜索框是否显示
		hide_show:function(id)
		{
			if(!id || id == 'undefined'){
				return false;
			}
			if(id.substr(0,1) != '#' && id.substr(0,1) !='.'){
				id = '#'+id;
			}
			if($(id).is(':hidden')){
				$(id).show();
				return true;
			}
			$(id).hide();
			return true;
		},

		/**
		 * 刷新父标签窗口
		 * @参数 url 要刷新的父标签网址
		**/
		reload:function(url)
		{
			if(!url || url == 'undefined'){
				return false;
			}
			url = url.replace(/\&\_noCache=[0-9\.]+/g,'');
			url = url.replace(webroot,'');
			top.$("#LAY_app_tabsheader li").each(function(i){
				if(!$(this).hasClass('layui-this')){
					var layid = $(this).attr('lay-id');
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
						if(layid.indexOf(url) != -1 || url.indexOf(layid) != -1){
							top.$('.layadmin-iframe').eq(i)[0].contentWindow.location.reload(true);
						}
					}
				}
			});
		},

		/**
		 * 跳转到标签页
		 * @参数 url 要跳转的标签页
		**/
		goto_tab:function(url)
		{
			if(!url || url == 'undefined'){
				return false;
			}
			url = url.replace(/\&\_noCache=[0-9\.]+/g,'');
			url = url.replace(webroot,'');
			top.$("#LAY_app_tabsheader li").each(function(i){
				var layid = $(this).attr('lay-id');
				if(layid){
					layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
					if(layid.indexOf(url) != -1){
						$(this).click();
					}
				}
			});
		},

		/**
		 * 关闭当前窗口
		**/
		close:function(url)
		{
			if(url && url != 'undefined'){
				this.reload(url);
				url = url.replace(/\&\_noCache=[0-9\.]+/g,'');
				url = url.replace(webroot,'');
			}
			var obj,obj_goto,obj_status = false,obj_goto_status = false;
			top.$("#LAY_app_tabsheader li").each(function(i){
				var layid = $(this).attr('lay-id');
				if($(this).hasClass('layui-this')){
					obj = $(this);
					obj_status = true;
				}else{
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
						if(url && url != 'undefined' && (layid.indexOf(url) != -1 || url.indexOf(layid) != -1)){
							obj_goto = $(this);
							obj_goto_status = true;
						}
					}
				}
			});
			setTimeout(function(){
				if(obj_goto_status){
					obj_goto.click();
				}
				if(obj_status){
					obj.find(".layui-tab-close").click();
				}
			}, 500);
		},
		title:function(title,url)
		{
			if(url && url != 'undefined'){
				url = url.replace(/\&\_noCache=[0-9\.]+/g,'');
			}
			var s = '';
			top.$("#LAY_app_tabsheader li").each(function(i){
				if(url && url != 'undefined'){
					var layid = $(this).attr('lay-id');
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
						layid = layid.replace(webroot,'');
						if(url.indexOf(layid) != -1){
							s = $(this).find("span").html();
							if(title && title != 'undefined'){
								$(this).find("span").html(title);
							}
						}
					}
				}else{
					if($(this).hasClass('layui-this')){
						s = $(this).find("span").html();
						if(title && title != 'undefined'){
							$(this).find("span").html(title);
						}
					}
				}
			});
			return s;
		},

		/**
		 * 随机码
		**/
		rand:function(id)
		{
			if(!id || id == 'undefine'){
				id = 'identifier';
			}
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			$(id).val($.phpok.rand(2,'letter')+""+$.phpok.rand(8,'fixed'));
		},

		card:function(obj)
		{
			var t = $(obj).parent().find('.layui-card-body');
			if(t.is(":hidden")){
				t.toggle(function(){
					$(obj).find("i.layui-icon").removeClass('layui-icon-right').addClass('layui-icon-down');
				});

			}else{
				t.toggle(function(){
					$(obj).find("i.layui-icon").removeClass('layui-icon-down').addClass('layui-icon-right');
				});
			}
		},
		vcode:function(obj)
		{
			var url = get_url('admin','vcode');
			var code = $(obj).find("input[type=password]").val();
			if(!code){
				$.dialog.alert(p_lang('二次密码不能为空'));
				return false;
			}
			url += '&code='+$.str.encode(code);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('验证通过'),function(){
					$.phpok.reload();
				}).lock();
			});
			return false;
		}
	};
})(jQuery);


$(document).ready(function(){
	layui.config({
		base: webroot+'static/admin/' //静态资源所在路径
	}).extend({
	    index: 'lib/index' //主入口模块
	}).use(['layer','form','laydate','index'],function(){
		layui.form.on('radio',function(data){
			$(data.elem).click();
		});
		window.setTimeout(function(){
			layui.form.render();
		}, 500);
	});
	document.addEventListener("keydown", function (e) {
		if (e.keyCode == 116) {
			e.preventDefault();
			$('a[layadmin-event=refresh]').click();
			//要做的其他事情
		}
	}, false);
	var clipboard = new Clipboard('.phpok-copy');
	clipboard.on('success', function(e){
		$.dialog.tips(p_lang('复制成功')).position('50%','10%').time(1);
		e.clearSelection();
	});
	clipboard.on('error', function(e){
		$.dialog.tips(p_lang('复制失败')).position('50%','10%').time(1);
	});
});