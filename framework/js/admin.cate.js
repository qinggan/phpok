/**
 * 分类相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2018年09月16日
**/

;(function($){
	$.admin_cate = {

		/**
		 * 保存分类操作
		**/
		save:function()
		{
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
						$.dialog.tips(tip).lock();
						$.admin.close(get_url('cate'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		save_more:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('cate','save_more'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('分类信息添加成功')).lock();
						$.admin.close(get_url('cate'));
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
		},
		pl_status:function(val)
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('未指定要操作的分类'));
				return false;
			}
			var url = get_url('cate','pl_status','ids='+$.str.encode(ids)+"&status="+val);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				var list = ids.split(",");
				for(var i in list){
					if(val == 1){
						$("#status_"+list[i]).removeClass("status0").addClass("status1");
					}else{
						$("#status_"+list[i]).removeClass("status1").addClass("status0");
					}
				}
				return true;
			})
		},

		/**
		 * 批量删除处理
		**/
		pl_delete:function()
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('未指定要操作的分类'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要批量删除选中的分类吗？如果存在子分类，删除操作将无效的！'),function(){
				var url = get_url('cate','pl_delete','ids='+$.str.encode(ids));
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('删除操作成功'),function(){
						$.phpok.reload();
					}).lock();
				})
			});
		},
		taxis:function(obj,id)
		{
			var val = $(obj).val();
			var old = $(obj).attr("data-old");
			if(old == val){
				return false;
			}
			$.phpok.json(get_url('cate','taxis'),function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				//更新成功后，将旧值写入到data-old属性中
				$(obj).attr("data-old",val);
				$.dialog.tips('排序更新成功');
			},{'id':id,'taxis':val});
			return false;
		}
	}

	//---
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			})
		}
	});
})(jQuery);