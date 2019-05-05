/**
 * 物流快递JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年4月30日
**/
;(function($){
	$.admin_express = {
		add:function(){
			$.dialog({
				'title':p_lang('请选择快递接口引挈'),
				'content':document.getElementById("express_select_info"),
				'ok':function(){
					var code = $("#code").val();
					if(!code){
						$.dialog.alert(p_lang('请选择要创建的快递引挈'));
						return false;
					}
					var url = get_url('express','set','code='+code);
					$.win(p_lang('添加物流快递'),url);
					return true;
				},
				'cancel':true
			});
		},
		edit:function(id){
			var title = p_lang('编辑物流')+"_#"+id;
			$.win(title,get_url('express','set','id='+id));
		},
		del:function(id){
			var text = $("#title_"+id).text();
			var title = p_lang('确定要删除物流快递：{title} 吗？','<span class="red">'+text+'</span>');
			$.dialog.confirm(title,function(){
				var url = get_url('express','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'));
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		save:function(){
			$("#postsave").ajaxSubmit({
				'url':get_url('express','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('物流信息保存成功'));
						var id = $("#id").val();
						if(!id){
							$.admin.reload(get_url('express'));
							$.admin.close(get_url('express'));
							return true;
						}
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);