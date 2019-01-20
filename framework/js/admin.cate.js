/**
 * 分类相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年09月16日
**/

;(function($){
	$.admin_cate = {

		/**
		 * 保存分类操作
		**/
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('cate','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						if(id && id > 0){
							var tip = p_lang('分类信息编辑成功');
						}else{
							var tip = p_lang('分类信息添加成功');
						}
						$.dialog.tips(tip,function(){
							$.admin.reload(get_url('cate'));
							$.admin.close();
						});
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 删除分类
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除此分类吗？{id}','<span class="red">#'+id+'</span>'),function(){
	            var url = get_url("cate","delete","id="+id);
	            $.phpok.json(url,function(rs){
		            if(rs.status){
			            $.dialog.tips(p_lang('分类删除成功'),function(){
				            $.phpok.reload();
			            }).lock();
			            return true;
		            }
		            $.dialog.alert(rs.info);
		            return false;
	            });
	        });
		},

		/**
		 * 添加扩展分类
		**/
		ext_add:function(id)
		{
			var val = $("#_tmp_select_add").val();
			if(!val){
				$.dialog.alert(p_lang('请选择要添加的扩展'));
				return false;
			}
			ext_add2(val,id);
		},
		status:function(id)
		{
			var url = get_url('cate','status','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					if(rs.info == '1'){
						$("#status_"+id).removeClass("status0").addClass("status1");
					}else{
						$("#status_"+id).removeClass("status1").addClass("status0");
					}
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			})
		}
	});
})(jQuery);