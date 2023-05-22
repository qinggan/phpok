/**
 * 云市场
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年4月28日
 * @更新 2023年4月28日
**/
;(function($){
	$.admin_yunmarket = {
		buy:function(id)
		{
			$.phpok.open(get_url('yunmarket','vip','id='+id));
		},
		config_save:function(obj)
		{
			var url = get_url('yunmarket','config_save');
			var lock = $.dialog.tips('正在提交中，请稍候…',100).lock();
			$.phpok.submit($(obj)[0],url,function(rs){
				if(!rs.status){
					lock.content(rs.info).time(2);
					return false;
				}
				lock.setting('close',function(){
					$.phpok.go(get_url('yunmarket'));
				});
				lock.content('操作成功').time(2);
				return true;
			});
			return false;
		},
		info:function(id,title)
		{
			var url = get_url('yunmarket','content','id='+id);
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'500px',
				'height':'500px'
			});
		},
		install:function(id,title,is_ext)
		{
			var tip = '确定要安装【<span class="red">'+title+'</span>】吗？<br/>';
			if(is_ext){
				tip += '<span class="red"><b>涉及到关联扩展会自动安装！</b></span><br/>';
			}
			tip += '点【确定】即同意安装';
			$.dialog.confirm(tip,function(){
				var url = get_url('yunmarket','install','id='+id);
				var lock = $.dialog.tips('正在安装，请稍候…',100).lock();
				$.phpok.json(url,function(rs){
					if(!rs.status){
						lock.content(rs.info).time(1.5);
						return false;
					}
					lock.setting('close',function(){
						if(rs.info == 'app'){
							$.win('应用中心',get_url('appsys'));
						}
						if(rs.info == 'plugin'){
							$.win('插件中心',get_url('plugin'));
						}
						$.phpok.reload();
					});
					lock.content('安装成功，请稍候…').time(1.5);
				});				
			});
			return false;
		},
		uninstall:function(id,title)
		{
			$.dialog.confirm('确定要卸载【'+title+'】，卸载后相关数据会直接删除',function(){
				var url = get_url('yunmarket','uninstall','id='+id);
				var lock = $.dialog.tips('正在卸载，请稍候…',100).lock();
				$.phpok.json(url,function(rs){
					if(!rs.status){
						lock.content(rs.info).time(1.5);
						return false;
					}
					lock.setting('close',function(){
						$.phpok.reload();
					});
					lock.content('卸载成功，请稍候…').time(1.5);
				});
			});
		},
		update:function(id,title)
		{
			$.dialog.confirm('确定要升级【'+title+'】',function(){
				var url = get_url('yunmarket','update','id='+id);
				var lock = $.dialog.tips('正在升级，请稍候…',100).lock();
				$.phpok.json(url,function(rs){
					if(!rs.status){
						lock.content(rs.info).time(1.5);
						return false;
					}
					lock.setting('close',function(){
						$.phpok.reload();
					});
					lock.content('升级成功，请稍候…').time(1.5);
				});
			});
		}
	}
})(jQuery);
