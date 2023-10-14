/**
 * 后台脚本_用于管理多语言，支持批量翻译等操作
 * @作者 phpok.com <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年10月13日 18时20分
**/
;(function($){
	$.admin_multi_language = {
		add:function()
		{
			$.dialog({
				'title':p_lang('添加语言包'),
				'padding':'0',
				'content':$("#add").html(),
				'ok':function(){
					var act = $.admin_multi_language.addok();
					if(act){
						return true;
					}
					return false;
				}
			});
		},
		addok:function()
		{
			var id = $("#id").val();
			if(!id){
				$.dialog.tips(p_lang('未配置标识'));
				return false;
			}
			var title = $("#title").val();
			if(!title){
				$.dialog.tips(p_lang('未配置名称'));
				return false;
			}
			$.phpok.json(get_url('multi_language','set'),function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('添加成功'),function(){
					$.phpok.reload();
				});
			},{'id':id,'title':title});
		},
		del:function(id,title)
		{
			$.dialog.confirm(p_lang('确定要删除语言 {0} 吗？删除后不能恢复',title),function(){
				$.phpok.json(get_url('multi_language','delete','id='+id),function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('操作成功'),function(){
						$.phpok.reload();
					}).lock();
				});
			});
		},
		edit:function(id,title)
		{
			$.dialog.prompt(p_lang('编辑语言标识 {0}',id),function(val){
				if(!val || val == 'undefined'){
					$.dialog.tips(p_lang('未配置名称'));
					return false;
				}
				if(val == title){
					$.dialog.tips(p_lang('名称一致，不需要修改'));
					return false;
				}
				$.phpok.json(get_url('multi_language','set'),function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('修改成功'),function(){
						$.phpok.reload();
					});
				},{'id':id,'title':val});
			},title);
		},
		/**
		 * 刷新中文语言包
		**/
		refresh:function()
		{
			$.dialog.confirm(p_lang('确定要更新中文基础包吗？'),function(){
				var url = get_url('multi_language','refresh');
				var act = $.dialog.tips(p_lang('正在更新中，请稍候…'),100).lock();
				$.phpok.json(url,function(rs){
					if(!rs.status){
						act.content(rs.info).time(2);
						return false;
					}
					act.content(p_lang('基础包更新成功')).time(2);
				});
			});
		}
	}
})(jQuery);
