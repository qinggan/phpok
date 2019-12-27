/**
 * 导航菜单管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月26日
**/
;(function($){
	$.admin_menu = {
		group:function()
		{
			var url = get_url('menu','group');
			$.dialog.open(url,{
				'title':p_lang('组管理'),
				'width':'500px',
				'height':'400px',
				'lock':true,
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_menu.group_save();
					return false;
				},
				'okVal':p_lang('提交保存')
			});
		},
		group_save:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('menu','group_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('操作成功',function(){
							opener.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		group_del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除菜单ID #[id] 吗？删除后不能恢复','<span class="red">'+id+'</span>'),function(){
				var url = get_url('menu','group_delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('菜单删除成功'));
					$("#menu_"+id).remove();
				});
			});
		},
		add:function(keyid,parent_id)
		{
			if(!keyid){
				$.dialog.alert('未指定ID');
				return false;
			}
			if(!parent_id || parent_id == 'undefined'){
				parent_id = 0;
			}
			var url = get_url('menu','set','group_id='+keyid+'&parent_id='+parent_id);
			var t = parent_id>0 ? p_lang('添加子菜单')+"_#"+parent_id : p_lang('添加菜单');
			$.dialog.open(url,{
				'title':t,
				'width':'700px',
				'height':'450px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_menu.save();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'lock':true,
				'cancel':true
			});
		},
		edit:function(id)
		{
			var url = get_url('menu','set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑菜单')+"_#"+id,
				'width':'700px',
				'height':'450px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_menu.save();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'lock':true,
				'cancel':true
			});
		},
		save:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('menu','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("input[name=id]").length > 0 ? p_lang('菜单编辑成功') : p_lang('菜单添加成功');
						$.dialog.alert(tip,function(){
							opener.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		title:function()
		{
			var pid = $("#pid-content").val();
			if(!pid){
				$.dialog.alert(p_lang('请选择主题项目'));
				return false;
			}
			var url = get_url('menu','titles','pid='+pid);
			$.dialog.open(url,{
				'title':p_lang('选择主题_#'+pid),
				'width':'700px',
				'height':'500px',
				'lock':true
			});
		},
		tolist:function(id)
		{
			var url = get_url('menu','','id='+id);
			$.phpok.go(url);
		},
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除菜单ID #[id] 吗？删除后不能恢复','<span class="red">'+id+'</span>'),function(){
				var url = get_url('menu','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$("#menu_"+id).remove();
				})
			});
		}
	}
})(jQuery);