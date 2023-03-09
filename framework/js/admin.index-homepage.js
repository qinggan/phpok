/**
 * 首页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月11日
**/
$(document).ready(function(){
	var r_menu = [[{
		'text':p_lang('刷新'),
		'func':function(){
			$.phpok.reload();
		}
	}],[{
		'text':p_lang('清空缓存'),
		'func': function() {top.$.admin_index.clear();}
	},{
		'text':p_lang('修改我的信息'),
		'func':function(){top.$.admin_index.me();}
	},{
		'text':p_lang('访问前台首页'),
		'func':function(){
			var url = "{$sys.www_file}?siteId={$session.admin_site_id}";
			url = $.phpok.nocache(url);
			window.open(url);
		}
	}],[{
		'text': p_lang('帮助说明'),
		'func': function() {
			top.$("a[layadmin-event=about]").click();
			return true;
		}
	}]];
	$(window).smartMenu(r_menu,{
		'name':'smart',
		'textLimit':8
	});
	window.addEventListener("message",function(e){
		if(e.origin != window.location.origin){
			return false;
		}
		if(e.data == 'badge'){
			$.admin.badge();
			return true;
		}
	}, false);
	//检测是否添加角标
	window.setTimeout(function(){
		$.admin.badge();
	}, 300);
});
