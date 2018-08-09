/**
 * 应用管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月07日
**/
;(function($){
	$.admin_appsys = {
		setting:function()
		{
			$.dialog.open(get_url('appsys','setting'),{
				'title':p_lang('APP应用配置'),
				'lock':true,
				'width':'500px',
				'height':'240px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_appsys.setting_save();
					return false;
				},
				'cancel':true
			});
		},
		setting_save:function()
		{
			var opener = $.dialog.opener;
			var url = get_url('appsys','setting_save');
			$("#post_save").ajaxSubmit({
				'url':url,
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						opener.$.dialog.tips(p_lang('配置信息成功'));
						$.dialog.close();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return true;
		},
		remote:function()
		{
			var tip = $.dialog.tips(p_lang('正在更新远程数据，请稍候…'),30)
			$.phpok.json(get_url('appsys','remote'),function(data){
				tip.close();
				if(data.status){
					$.dialog.alert('远程数据更新完成',function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
		},
		install:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('确定要安装应用 {title} 吗？请耐心等待安装安全','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','install','id='+id);
				var tip = $.dialog.tips('正在安装应用，请稍候…',100);
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('应用发装成功，涉及到菜单项请全局刷新');
						$.dialog.alert(info,function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			});
		},
		uninstall:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('确定要卸载应用 {title} 吗？<br>卸载过程不会考虑应用与应用之间的关联，卸载前请仔细确认','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','uninstall','id='+id);
				var tip = $.dialog.tips('正在卸载应用，请稍候…',100);
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('应用卸载成功');
						$.dialog.alert(info,function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			});
		}
	}
})(jQuery);