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
				"height":260,
				"lock":true,
				'move':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_me.setting_submit();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'cancel':true
			});
		},
		pass:function()
		{
			
			$.dialog.open(get_url("me","pass"),{
				"title":p_lang('管理员密码修改'),
				"width":500,
				"height":240,
				"lock":true,
				'move':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_me.pass_submit();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'cancel':true
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


$(document).ready(function(){
	//监听事件
	window.addEventListener("message",function(e){
		if(e.origin != window.location.origin){
			return false;
		}
		if(e.data == 'close'){
			$('.aui_close').click();
			return true;
		}
	}, false);
	

	$.admin_index.pendding();
	layui.config({
	  	base: webroot+'static/admin/' //静态资源所在路径
	}).extend({
		index: 'lib/index' //主入口模块
	}).use('index');
});

