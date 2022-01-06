/**
 * 收藏夹相关JS动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
;(function($){
	$.phpok_app_fav = {
		act:function(id,obj)
		{
			var url = api_url('fav','act','id='+id);
			$.phpok.json(url,function(data){
				if(data.status){
					if(data.info == 'add'){
						$(obj).val(p_lang('加入收藏成功'));
						window.setTimeout(function(){
							$(obj).val('已收藏')
						}, 1000);
					}
					if(data.info == 'delete'){
						$(obj).val(p_lang('取消收藏成功'));
						window.setTimeout(function(){
							$(obj).val('加入收藏')
						}, 1000);
					}
					return true;
				}
				$.dialog.alert(data.info);
				return false;				
			});
		},
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这条收藏记录吗？'),function(){
				var url = api_url('fav','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			})
		}
	}
})(jQuery);