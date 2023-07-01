/**
 * 应用管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
		help_save:function(obj)
		{
			$(obj).ajaxSubmit({
				'url':get_url('appsys','help_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('编写成功');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
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
		},
		delete_apps:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('确定要删除应用 {title} 吗？<br>删除后不可恢复，请确认已备份过相应的文件','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','delete','id='+id);
				var tip = $.dialog.tips('正在删除应用，请稍候…',100).lock();
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('应用删除成功');
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
		add:function()
		{
			var url = get_url('appsys','add');
			$.dialog.open(url,{
				'title':p_lang('创建新应用'),
				'lock':true,
				'width':'700px',
				'height':'370px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_appsys.create();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'cancel':true,
				'cancelVal':p_lang('取消关闭')
			});
		},
		create:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('appsys','create'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('应用创建成功，请开发人员进行开发操作'),function(){
							opener.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		export_zip:function(id)
		{
			$.phpok.go(get_url('appsys','export','id='+id));
		},
		backup_zip:function(id)
		{
			var url = get_url('appsys','backup','id='+id);
			var obj = $.dialog.tips(p_lang('正在备份中…'),100).lock();
			$.phpok.json(url,function(rs){
				obj.close();
				if(rs.status){
					$.dialog.tips(p_lang('备份成功')).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		import_zip:function()
		{
			var url = get_url('appsys','import');
			$.dialog.open(url,{
				'title':p_lang('导入应用'),
				'width':'500px',
				'height':'150px',
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
				'okVal':p_lang('开始上传'),
				'cancel':true
			})
		},
		backup_delete:function(id)
		{
			var tip = p_lang('确定要删除备份文件{file}吗？删除后是不能恢复的','<span class="red">'+id+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url('appsys','backup_delete','id='+$.str.encode(id));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('备份文件删除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			})
		},
		taxis:function(obj,id)
		{
			var taxis = $(obj).text();
			$.dialog.prompt(p_lang('修改排序，排序越前相应的HTML前点优先处理'),function(val){
				if(!val || val<1){
					$.dialog.alert('排序值不能小于1');
					return false;
				}
				var url = get_url('appsys','taxis','id='+id+"&taxis="+val);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips('排序成功',function(){
						$.phpok.reload();
					}).time(1.5).lock();
				})
			},taxis);
		},
		help:function(title,id)
		{
			$.win(p_lang('开发帮助 - {title}',title),get_url('appsys','showhelp','id='+id+'&type=admin'));
		},
		www_help:function(title,id)
		{
			$.win(p_lang('使用帮助 - {title}',title),get_url('appsys','showhelp','id='+id+'&type=www'));
		}
	}
})(jQuery);