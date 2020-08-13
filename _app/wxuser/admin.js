/**
 * 后面页面脚本_登记微信平台里所有会员，包括开放平台，公众平台及小程序平台
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月26日 03时25分
**/
;(function($){
	$.admin_wxuser = {
		unlock:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('未指定要删除的会员'));
				}
			}
			$.dialog.confirm(p_lang('确定要解除ID为{id}的会员账号吗？',{"id":' <span class="red">'+id+'</span> '}),function(){
				var url = get_url('wxuser','unlock','id='+id);
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
		lock:function(id)
		{
			$.dialog.prompt(p_lang('请输入ID为{id}要绑定的会员账号',{"id":' <span class="red">'+id+'</span> '}),function(val){
				if(!val){
					$.dialog.alert(p_lang('会员账号不能为空'));
					return false;
				}
				var url = get_url('wxuser','lock','id='+id+"&user="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('会员绑定成功'),function(){
						$.phpok.reload();
					}).lock();
					return false;
				});
			});
		},
		del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('未指定要删除的会员'));
				}
			}
			$.dialog.confirm(p_lang('确定要解除ID为{id}的会员账号吗？',{"id":' <span class="red">'+id+'</span> '}),function(){
				var url = get_url('wxuser','delete','id='+id);
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
