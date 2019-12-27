/**
 * 后台公共JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年09月16日
**/
function alt_open(id,note)
{
	if(!id || id == "undefined") return false;
	if(!note || note == "undefined")
	{
		note = $("#"+id).attr("alt") ? $("#"+id).attr("alt") : $("#"+id).attr("title");
		if(!note || note == "undefined")
		{
			return false;
		}
	}
	$.dialog({
		"id": "phpok_alt",
		"title": false,
		"cancel":false,
		"padding":"10px 10px",
		"follow": document.getElementById(id),
		"content":note
	});
}

function alt_close()
{
	$.dialog.list["phpok_alt"].close();
}

//通用更新排序
function taxis(baseurl,default_value)
{
	var url = baseurl;
	if(!default_value || default_value == "undefined") default_value = "0";
	var id_string = $.input.checkbox_join();
	if(!id_string || id_string == "undefined"){
		$.dialog.alert("没有指定要更新的排序ID！");
		return false;
	}
	//取得排序值信息
	var id_list = id_string.split(",");
	var id_leng = id_list.length;
	for(var i=0;i<id_leng;i++){
		var taxis = $("#taxis_"+id_list[i]).val();
		if(!taxis) taxis = default_value;
		url += "&taxis["+id_list[i]+"]="+$.str.encode(taxis);
	}
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.dialog.alert("排序更新完成！",function(){
			$.phpok.reload();
		});
	}else{
		alert(rs.content);
		return false;
	}
}

/**
 * 通用排序
**/
function phpok_taxis(obj,url)
{
	if(!url || url == 'undefined' || !obj || obj == 'undefined'){
		return false;
	}
	var taxis = $(obj).val();
	url += "&taxis="+taxis;
	$.phpok.json(url,function(rs){
		if(rs.status){
			$.dialog.tips(p_lang('排序更新成功')).follow($(obj)[0]);
			return false;
		}
		$.dialog.alert(rs.info);
		return false;
	});
}

/**
 * 通用状态更新
**/
function phpok_status(id,url)
{
	if(!url || url == "undefined" || !id) return false;
	url += "&id="+$.str.encode(id);
	$.phpok.json(url,function(rs){
		if(rs.status && rs.status != 'error'){
			var info = (rs.info && rs.info != 'undefined') ? rs.info : (rs.content ? rs.content : '0');
			if(info == 1){
                var status_css="";
                var old_cls = "layui-btn-danger";
                $("#status_"+id).val("启用");
            }else{
                var status_css="layui-btn-danger";
                var old_cls = "";
                $("#status_"+id).val("停用");
            }
			$("#status_"+id).removeClass(old_cls).addClass(status_css);
			return true;
		}
		var info = (rs.info && rs.info != 'undefined') ? rs.info : (rs.content ? rs.content : 'Error');
		layer.alert(info);
		return false;
	})
}

/* 通用表单自动存储 */
// formid，要自动存储的表单ID
// type，要自动存储的类型，目前仅支持 cate list两种
// func，返回执行的函数
function autosave(formid,type,func)
{
	if(!type || type == "undefined"){
		type = "list";
	}
	if(!func || func == "undefined"){
		func = "autosave_callback";
	}
	var str = $("#"+formid).serialize();
	var url = get_url("auto") + "&__type="+type;
	//通过POST存储数据
	$.post(url,str,function(rs){(func)(rs);},"json");
}

//弹出图片选择窗口
function phpok_pic(id)
{
	if(!id || id == "undefined"){
		$.dialog.alert(p_lang('未指定ID'));
		return false;
	}
	var url = get_url("open","input",'id='+id);
	$.dialog.open(url,{
		title: p_lang('图片管理器'),
		lock : true,
		width: "650px",
		height: "450px"
	});
}

// 预览图片
function phpok_pic_view(id)
{
	var url = $("#"+id).val();
	if(!url || url == "undefined"){
		$.dialog.alert("图片不存在，请在表单中填写图片地址");
	}else{
		top.$.dialog({
			'title':'预览',
			'content':'<img src="'+url+'" border="0" style="max-width:500px" />',
			'lock':true,
			'ok':function(){},
			'height':350,
			'width':500,
			'okVal':'关闭预览'
		});
	}
}

//弹出窗口，选择模板
function phpok_tpl_open(id)
{
	var url = get_url("tpl","open") + "&id="+id;
	$.dialog.open(url,{
		title: "模板选择",
		lock : true,
		width: "700px",
		height: "70%",
		resize: false
	});
}





function phpok_admin_orderby(id,val)
{
	$.dialog({
		"title":"排序设置",
		"content":'<div><label for="_phpok_tmp_desc_'+id+'"><input type="radio" name="_phpok_tmp_desc_asc_'+id+'" value="DESC" checked id="_phpok_tmp_desc_'+id+'"/>倒序↓，数值从高排到低，示例：Z→A，9→1</label></div><div><label for="_phpok_tmp_asc_'+id+'"><input type="radio" value="ASC" name="_phpok_tmp_desc_asc_'+id+'" id="_phpok_tmp_asc_'+id+'"/>正序↑，数值从低排到高，示例：A→Z，1→9</label></div>',
		'lock':true,
		"ok":function(){
			var desc_asc = $("input[name=_phpok_tmp_desc_asc_"+id+"]:checked").val();
			if(!desc_asc)
			{
				alert("请选择排序方式");
				return false;
			}
			val += " "+desc_asc;
			var str = $("#"+id).val();
			if(str)
			{
				str += ","+val;
			}
			else
			{
				str = val;
			}
			$("#"+id).val(str);
		}
	});
}

function goto_site(id,oldid)
{
	$.dialog.confirm(p_lang('确定要切换到网站')+"<span style='color:red;font-weight:bold;'>"+$('#top_site_id').find("option:selected").text()+"</span>",function(){
		var url = get_url("index","site") + "&id="+id.toString();
		direct(url);
	},function(){
		$("#top_site_id").val(oldid);
	});
}

//添加自定义字段
function ext_add(module)
{
	var url = get_url('ext','create','id='+$.str.encode(module));
	$.dialog.open(url,{
		'title':'创建扩展字段',
		'width':'750px',
		'height':'580px',
		'resize':false,
		'lock':true,
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal': '提交配置'
	});
	return true;
}

function ext_add2(id,module)
{
	var url = get_url("ext","add","module="+module+"&id="+id);
	$.phpok.json(url,function(rs){
		if(rs.status){
			$.phpok.reload();
			return true;
		}
		$.dialog.alert(rs.info);
		return false;
	});
}

/**
 * 删除扩展字段
 * @参数 id 要删除的ID
 * @参数 module 指定模块
 * @参数 title 标题，用于提示说明
**/
function ext_delete(id,module,title)
{
	$.dialog.confirm(p_lang('确定要删除扩展字段：{title} 吗？<br>删除后是不能恢复的！','<span class="red">'+title+'</span>'),function(){
		var url = get_url('ext','delete','module='+$.str.encode(module)+"&id="+id);
		$.phpok.json(url,function(data){
			if(data.status){
				$.phpok.reload();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		})
	})
}

//编辑字段
function ext_edit(id,module)
{
	var url = get_url("ext","edit",'id='+id);
	url += "&module="+$.str.encode(module);
	$.dialog.open(url,{
		"title" : "编辑扩展字段属性",
		"width" : "700px",
		"height" : "95%",
		"win_min":false,
		"win_max":false,
		"resize" : false,
		"lock" : true,
		'okVal': '提交',
		'ok': function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		}
	});
}

//前台常用JS函数封装
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
			top.$("#LAY_app_tabsheader li").each(function(i){
				if(!$(this).hasClass('layui-this')){
					var layid = $(this).attr('lay-id');
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
					}
					var chk = webroot+layid;
					if(chk.indexOf(url) != -1){
						top.$('.layadmin-iframe').eq(i)[0].contentWindow.location.reload(true);
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
			var li_num = 0;
			top.$("#LAY_app_tabsheader li").each(function(i){
				if(!$(this).hasClass('layui-this')){
					var layid = $(this).attr('lay-id');
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
					}
					var chk = webroot+layid;
					if(chk.indexOf(url) != -1 && li_num<1){
						li_num = i;
					}
				}
			});
			if(li_num>0){
				top.$("#LAY_app_tabsheader li").eq(li_num).click();
			}
		},

		/**
		 * 关闭当前窗口
		**/
		close(url)
		{
			var self = this;
			window.setTimeout(function(){
				top.layui.admin.events.closeThisTabs();
				if(url && url != 'undefined'){
					self.goto_tab(url);
				}
			},500);
		},

		title:function(title,url)
		{
			top.$("#LAY_app_tabsheader li").each(function(i){
				if(url && url != 'undefined'){
					var layid = $(this).attr('lay-id');
					if(layid){
						layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
					}
					var chk = webroot+layid;
					if(chk.indexOf(url) != -1){
						$(this).find("span").html(title);
					}
				}else{
					if($(this).hasClass('layui-this')){
						$(this).find("span").html(title);
					}
				}
			});
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
		}
	};
})(jQuery);

$(document).ready(function(){
	//计划任务自动执行
	window.setTimeout(function(){
		$.phpok.json(api_url('task'),function(rs){
			return true;
		});
	}, 600);
	var clipboard = new Clipboard('.phpok-copy');
	clipboard.on('success', function(e){
		$.dialog.tips(p_lang('复制成功'));
		e.clearSelection();
	});
	clipboard.on('error', function(e){
		$.dialog.tips(p_lang('复制失败'));
	});
	var r_menu_in_copy = [{
		'text':p_lang('复制'),
		'func':function(){
			var info = $("#smart-phpok-copy-html").val();
			if(window.clipboardData && info != ''){
				window.clipboardData.setData("Text", info);
				$.dialog.tips(p_lang('文本复制成功，请按 CTRL+V 粘贴'));
				return true;
			}
			if(document.execCommand && info != ''){
				$("#smart-phpok-copy-html").focus().select();
				document.execCommand("copy",false,null);
				$.dialog.tips(p_lang('文本复制成功，请按 CTRL+V 粘贴'));
				return true;
			}
			$.dialog.tips(p_lang('复制失败，请按 CTRL+C 进行复制操作'));
			return true;
		}
	},{
		'text':p_lang('刷新'),
		'func':function(){
			$.phpok.reload();
		}
	}];
	var r_menu_not_copy = [{
		'text':p_lang('刷新'),
		'func':function(){
			$.phpok.reload();
		}
	}];
	var r_menu = [[{
		'text':p_lang('刷新'),
		'func':function(){
			$.phpok.reload();
		}
	}],[{
		'text':p_lang('清空缓存'),
		'func': function() {
			top.$.win(p_lang('清空缓存'),get_url('index','cache'));
		}
	},{
		'text':p_lang('访问网站首页'),
		'func':function(){
			var url = top.$(".layui-icon-website").parent().attr("href");
			if(url){
				window.open(url);
			}else{
				window.open(webroot);
			}

		}
	}],[{
		'text':p_lang('网页属性'),
		'func':function(){
			var url = window.location.href;
			//去除随机数
			url = url.replace(/\_noCache=[0-9\.]+/g,'');
			if(url.substr(-1) == '&' || url.substr(-1) == '?'){
				url = url.substr(0,url.length-1);
			}
			top.$.dialog({
				'title':p_lang('网址属性'),
				'content':p_lang('网址：')+url+'<br /><div style="text-indent:36px"><a href="'+url+'" target="_blank" class="red">'+p_lang('新窗口打开')+'</a></div>',
				'lock':true,
				'drag':false,
				'fixed':true
			});
		}
	},{
		'text': p_lang('新窗口打开'),
		'func': function() {
			var url = window.location.href;
			//去除随机数
			url = url.replace(/\_noCache=[0-9\.]+/g,'');
			if(url.substr(-1) == '&' || url.substr(-1) == '?'){
				url = url.substr(0,url.length-1);
			}
			window.open(url);
		}
	}],[{
		'text': p_lang('帮助说明'),
		'func': function() {
			top.$("a[layadmin-event=about]").click();
		}
	}]];
	$(window).smartMenu(r_menu,{
		'name':'smart',
		'textLimit':8,
		'beforeShow':function(){
			$.smartMenu.remove();
			r_menu[0] = r_menu_not_copy;
			if(!document.queryCommandSupported('copy')){
				return true;
			}
			var info = window.getSelection ?  (window.getSelection()).toString() : (document.selection.createRange ? document.selection.createRange().text : '');
			if(info == '' && $("input[type=text]:focus").length>0){
				obj = $("input[type=text]:focus")[0];
				info = obj.value.substring(obj.selectionStart,obj.selectionEnd);
			}
			if(info == '' && $("textarea:focus").length>0){
				obj = $("textarea:focus")[0];
				info = obj.value.substring(obj.selectionStart,obj.selectionEnd);
			}
			if(info){
				info = info.replace(/<.+>/g,'');
			}
			if(info != ''){
				$("#smart-phpok-copy-html").remove();
				var html = '<input type="text" id="smart-phpok-copy-html" value="'+info+'" style="position:absolute;left:-9999px;top:-9999px;" />'
				$('body').append(html);
				r_menu[0] = r_menu_in_copy;
			}
		}
	});
	//如果有加载layui，执行这个
	if(typeof layui != 'undefined'){
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
	}
});
$(document).keydown(function(e){
	window.history.forward(1);
	history.pushState(null, null, document.URL);
	window.addEventListener('popstate', function () {
	    history.pushState(null, null, document.URL);
	});
});


