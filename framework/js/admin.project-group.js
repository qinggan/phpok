/**
 * 项目组管理
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年6月4日
**/
;(function($){
	$.admin_project_group = {
		del:function(id)
		{
			if(!id == 'default'){
				$.dialog.alert(p_lang('系统栏目不允许删除'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要删除标识为 {key} 的项目组吗？删除后对应的项目组内容会合并到默认组','<span style="color:red">'+id+'</span>'),function(){
				$.phpok.json(get_url('project','group_del','id='+id),function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('删除成功')).lock();
					$.phpok.reload();
				});
			});
			return false;
		},
		set:function(obj)
		{
			var k = $(obj).find('input[name=key]').val();
			if(!k){
				$.dialog.alert(p_lang('系统异常，未找到标识'));
				return false;
			}
			var n = $(obj).find('input[name=name]').val();
			if(!n){
				$.dialog.alert(p_lang('名称不能为空'));
				return false;
			}
			$.phpok.json(get_url('project','group_save'),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('编辑成功')).lock();
				return false;
			},{
				'id':k,
				'title':n,
				'act':'edit'
			});
			return false;
		},
		add:function(obj)
		{
			var k = $(obj).find('input[name=key]').val();
			if(!k){
				$.dialog.alert(p_lang('系统异常，未找到标识'));
				return false;
			}
			var n = $(obj).find('input[name=name]').val();
			if(!n){
				$.dialog.alert(p_lang('名称不能为空'));
				return false;
			}
			$.phpok.json(get_url('project','group_save'),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('添加成功')).lock();
				$.phpok.reload();
				return false;
			},{
				'id':k,
				'title':n,
				'act':'add'
			});
			return false;
		}
	}
})(jQuery);