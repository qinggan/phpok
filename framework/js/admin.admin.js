/**
 * 管理员的增删查改
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月02日
**/
;(function($){
	$.admin_admin = {
		set:function(id)
		{
			if(id && id != 'undefined'){
				var url = get_url('admin','set','id='+id);
				var title = p_lang('编辑管理员') + " #"+id;
			}else{
				var url = get_url('admin','set');
				var title = p_lang('添加管理员');
			}
			top.$.win(title,url);
		},
		status:function(id)
		{
			var url = get_url("admin","status","id="+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					if(rs.info == '1'){
	                	$("#status_"+id).val(p_lang('启用')).removeClass('layui-btn-danger');
					}else{
	                	$("#status_"+id).val(p_lang('停用')).addClass('layui-btn-danger');
					}
					return true;
				}
				if(!rs.info){
					rs.info = p_lang('设置管理员状态错误');
				}
				layer.alert(rs.info);
				return true;
			});
		},
		del:function(id,title)
		{
			var tip = p_lang('确定要删除管理员 {title} 吗？',"<span class='red'>"+title+"</span>");
			layer.confirm(tip,function(index){
				var url = get_url("admin","delete","id="+id);
				$.phpok.json(url,function(data){
					if(data.status){
						layer.msg(p_lang('管理员删除成功'));
						$("#admin_tr_"+id).remove();
						layer.close(index);
						return true;
					}
					layer.alert(data.info);
					return false;
				})
			});
		},
		if_system:function(val)
		{
			if(val && val == 1){
	            $("#sysmenu_html").hide();
	        }else{
	            $("#sysmenu_html").show();
	        }
		},
		save:function()
	    {
		    $(".layui-form").ajaxSubmit({
		    	'url':get_url('admin','save'),
		    	'type':'post',
		    	'dataType':'json',
		    	'success':function(rs){
		    		if(rs.status){
			    		var id = $("#id").val();
			    		var tipinfo = (id && id != 'undefined') ? p_lang('编辑成功') : p_lang('管理员添加成功');
			    		$.admin.reload(get_url('admin'));
			    		layer.msg(tipinfo,{time:1000},function(){
				    		top.layui.admin.events.closeThisTabs();
			    		});
		    			return false;
		    		}
		    		$.dialog.alert(rs.info);
		    		return false;
		    	}
		    });
		    return false;
	    }
	}
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			});
		}
	});
})(jQuery);