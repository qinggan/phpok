/**
 * 后面页面脚本_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
;(function($){
	$.admin_weixin = {
		config_save:function()
		{
			lock = $.dialog.tips(p_lang('正在保存中，请稍候…'),100).lock();
			$("#post_save").ajaxSubmit({
				'url':get_url('weixin','config_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						lock.content('数据保存成功').time(2);
						return true;
					}
					lock.close();
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		user_unlock:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('未指定要删除的用户'));
				}
			}
			$.dialog.confirm(p_lang('确定要解除ID为{id}的用户账号吗？',{"id":' <span class="red">'+id+'</span> '}),function(){
				var url = get_url('weixin','user_unlock','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('账号解除成功'),function(){
						$.phpok.reload();
					}).lock();
				})
			});
		},
		user_lock:function(id)
		{
			$.dialog.prompt(p_lang('请输入ID为{id}要绑定的用户账号',{"id":' <span class="red">'+id+'</span> '}),function(val){
				if(!val){
					$.dialog.alert(p_lang('用户账号不能为空'));
					return false;
				}
				var url = get_url('weixin','user_lock','id='+id+"&user="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('用户绑定成功'),function(){
						$.phpok.reload();
					}).lock();
					return false;
				});
			});
		},
		user_del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('未指定要删除的用户'));
				}
			}
			$.dialog.confirm(p_lang('确定要删除ID为{id}的用户账号吗？',{"id":' <span class="red">'+id+'</span> '}),function(){
				var url = get_url('weixin','user_delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('账号删除成功'),function(){
						$.phpok.reload()
					}).lock();
				})
			});
		}
	}
})(jQuery);
