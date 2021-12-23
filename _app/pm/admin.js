/**
 * 后面页面脚本_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
;(function($){
	$.admin_pm = {
		save:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('pm','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('发布成功',function(){
							$.admin.close(get_url('pm'));
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('未指定要删除的ID'));
					return false;
				}
			}
			$.dialog.confirm(p_lang('确定要删除指定的短消息吗？'),function(){
				var url = get_url('pm','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('短消息删除成功')).lock();
					$.phpok.reload();
				})
			})
		}
	}
	$(document).ready(function(){
		layui.laydate.render({
			elem:'#startdate'
		});
		layui.laydate.render({
			elem:'#stopdate'
		});
	});
})(jQuery);
