/**
 * 支付管理相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年05月09日
**/
;(function($){
	$.admin_payment = {
		group_save:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('payment','groupsave'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("#id").length > 0 ? p_lang('编辑支付方案成功') : p_lang('添加支付方案成功');
						$.dialog.alert(tip,function(){
							$.phpok.go(get_url('payment'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		group_delete:function(id,title)
		{
			var tip = p_lang("确定要删除支付组【{title}】吗？<br />删除后是不能恢复的！","<span class='red'>"+title+"</span>");
			$.dialog.confirm(tip,function(){
				var url = get_url('payment','groupdel','id='+id);
				$.phpok.json(url,function(rs){
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
		},
		add:function(gid)
		{
			var url = get_url('payment','set','gid='+gid);
			$.dialog({
				'title':p_lang('请选择要支付的类型'),
				'content':document.getElementById("payment_select_info"),
				'ok':function(){
					var code = $("#code").val();
					if(!code){
						alert(p_lang('请选择要创建的支付引挈'));
						return false;
					}
					url += "&code="+code;
					$.phpok.go(url);
					return true;
				},
				'cancel':true
			});
		},
		del:function(id,title)
		{
			var tip = p_lang("确定要删除支付方案 {title} 吗？删除后是不能恢复的","<span class='red'>"+title+"</span>");
			$.dialog.confirm(tip,function(){
				var url = get_url('payment','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('支付方案删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		check:function()
		{
			if(!$("#title").val()){
				$.dialog.alert(p_lang('支付名称不能为空'));
				return false;
			}
			if(!$("#code").val()){
				$.dialog.alert(p_lang('支付引挈异常，请重新操作'),function(){
					$.phpok.go(url);
				});
				return false;
			}
			return true;
		},
		taxis:function(obj)
		{
			var oldval = $(obj).text();
			var id = $(obj).attr('data');
			var type = $(obj).attr("type");
			$.dialog.prompt(p_lang('请填写新的排序'),function(val){
				val = parseInt(val);
				if(!val || val<1){
					$.dialog.alert(p_lang('排序仅限数字，不能为空'));
					return false;
				}
				if(val != oldval){
					var url = get_url('payment','taxis','type='+type+"&id="+id+"&taxis="+val);
					$.phpok.json(url,function(rs){
						if(rs.status){
							$.dialog.tips(p_lang('更新排序成功'),function(){
								$.phpok.reload();
							}).lock();
							return true;
						}
						$.dialog.alert(rs.info);
						return false;
					});
					return true;
				}
				$.dialog.tips(p_lang('值一样，不用更新'));
			},oldval);
		}
	}
})(jQuery);

