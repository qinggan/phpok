/**
 * 后台新版公共页代码
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年08月24日
**/

layui.config({
	base: webroot+'static/admin/' //静态资源所在路径
}).extend({
    index: 'lib/index' //主入口模块
}).use(['layer','form','laydate','index'],function(){
	layui.form.on('radio',function(data){
		$(data.elem).click();
	});
});

$(document).ready(function(){
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
		'func': function() {top.$.admin_index.clear();}
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
});

