/**
 * 后台首页涉及到的样式
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年08月13日
**/
;(function($){
	$.admin_index = {
		site:function()
		{
			$.dialog.open(get_url('site','add'),{
				'title': p_lang('添加站点')
				,'lock': true
				,'width': '450px'
				,'height': '150px'
				,'resize': false
				,'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
				,'okVal':p_lang('添加新站点')
				,'cancel':true
			});
		},
		me:function()
		{
			$.dialog.open(get_url("me","setting"),{
				"title":p_lang('修改管理员信息'),
				"width":600,
				"height":500,
				"lock":true,
				'move':false,
				'is_max':false
			});
		},
		logout:function()
		{
			$.dialog.confirm(p_lang('您确定要退出吗？'),function(){
				$.phpok.go(get_url("logout"));
			});
		},
		clear:function()
		{
			var obj = $.dialog.tips(p_lang('请稍候，正在执行'),100)
			$.phpok.json(get_url("index","clear"),function(data){
				obj.close();
				if(data.status){
					$.dialog.alert(p_lang('缓存清空完成'));
					return true;
				}
				$.dialog.alert(rs.info);
				return true;
			});
		},
		lang:function(val)
		{
			$.phpok.go(get_url("index",'','_langid='+val));
		},
		pendding:function()
		{
			$.phpok.json(get_url('index','pendding'),function(rs){
				$("em.toptip").remove();
				if(rs.status && rs.info){
					var list = rs.info;
					var html = '<em class="toptip">{total}</em>';
					var total = 0;
					for(var key in list){
						if(key == 'update_action'){
							$.admin_index.update();
						}else{
							if(list[key]['id'] == 'user' || list[key]['id'] == 'reply' || list[key]['id'] == 'update'){
								$("li[appfile="+list[key]['id']+"] a").append(html.replace('{total}',list[key]['total']));
							}else{
								$("li[pid="+list[key]['id']+"] a").append(html.replace('{total}',list[key]['total']));
								total = parseInt(total) + parseInt(list[key]['total']);
							}
						}
					}
					if(total>0){
						$("li[appfile=list] a").append(html.replace('{total}',total));
					}
					window.setTimeout(function(){
						$.admin_index.pendding();
					}, 10000);
				}else{
					window.setTimeout(function(){
						$.admin_index.pendding();
					}, 12000);
				}
			});
		},
		update:function()
		{
			$.phpok.json(get_url('update','check'),function(data){
				if(data.status == 'ok'){
					$.dialog.notice({
						title: '友情提示',
						width: 220,// 必须指定一个像素宽度值或者百分比，否则浏览器窗口改变可能导致artDialog收缩
						content: '您的程序有新的更新，为了保证系统安全，建议您及时更新程序',
						icon: 'face-smile',
						time: 10
					});
				}
			});
		}
	}
})(jQuery);

//添加站点信息
function add_site()
{
	$.admin_index.site();
}


$(document).ready(function(){
	$.admin_index.pendding();
	//判断是否显示
	$(window).click(function(e){
		var e = e || window.event;
		var obj = e.srcElement || e.target;
		if(obj.id == 'top-menu-a'){
			var is_hidden = $("#top-menu").is(":hidden");
			if(is_hidden){
				$('#top-menu').show();
			}else{
				$('#top-menu').hide();
				$(".second_ul").hide();
			}
		}else{
			$('#top-menu').hide();
			$(".second_ul").hide();
		}
	});
	$("li[name=subtree]").mouseover(function(){
		$(".second_ul").hide();
		$(".second_ul",this).show();
	});
	$(document).keydown(function(e){
		if (e.keyCode == 8){
			return false;
		}
	});
	//自定义右键
	var r_menu = [[{
		'text':p_lang('刷新网页'),
		'func':function(){
			$.phpok.reload();
		}
	},{
		'text': p_lang('清空缓存'),
		'func': function() {
			$.admin_index.clear();
		}    
	},{
		'text':p_lang('修改我的信息'),
		'func':function(){
			$.admin_index.me();
		}
	},{
		'text': p_lang('显示桌面'),
		'func': function() {
			$.desktop.tohome();
		}    
	}],[{
		'text':p_lang('关于PHPOK'),
		'func':function(){
			$.dialog({
				'title':p_lang('关于PHPOK'),
				'lock':true,
				'drag':false,
				'fixed':true,
				'content':'PHPOK企业站系统采用PHP+MySQL架构，基于LGPL协议开源并且免费。<br />本程序支持分类，项目，站点信息，模块等数据自定义<br />程序无任何内置广告代码<br />在使用过程序中，有任何问题，均可以登录 <a href="http://www.phpok.com/help.html" target="_blank" class="red">www.phpok.com/help.html</a> 查阅<br />如果您认可并打算捐助我们，点这里查看我们的收款账号：<a href="http://www.phpok.com/pay.html" target="_blank"style="color:red;">www.phpok.com/pay.html</a>'
			});
		}
	}]];
	$(window).smartMenu(r_menu,{'textLimit':8});
});