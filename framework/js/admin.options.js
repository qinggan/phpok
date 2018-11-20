/**
 * 商品属性
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月14日
**/
;(function($){
	$.admin_options = {
		add:function()
		{
			var url = get_url('options','save');
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('添加成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		update:function(id)
		{
			var url = get_url('options','save','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('编辑成功'));
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('确定要删除产品属性 {title} 吗？删除后，产品已使用此属性相关信息也会删除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('options','delete','id='+id),function(data){
					if(data.status){
						$.dialog.tips(p_lang('删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		info_add:function(aid)
		{
			var url = get_url('options','save_values','aid='+aid);
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			var pic = $("#pic_0").val();
			if(pic){
				url += "&pic="+pic;
			}
			var val = $("#val_0").val();
			if(val){
				url += "&val="+val;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('添加成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;					
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		info_update:function(id)
		{
			var url = get_url('options','save_values','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			var pic = $("#pic_"+id).val();
			if(pic){
				url += "&pic="+pic;
			}
			var val = $("#val_"+id).val();
			if(val){
				url += "&val="+val;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('编辑成功'));
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		info_del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('确定要删除产品属性 {title} 吗？删除后，产品已使用此属性相关信息也会删除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('options','delete_values','id='+id),function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);

