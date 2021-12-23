/**
 * 前台页面脚本_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
;(function($){
	$.phpok_app_social = {
		idol_add:function(id)
		{
			var url = api_url('social','idol','type=add&id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('关注成功',function(){
					$.phpok.reload();
				}).lock();
			});
		},
		idol_del:function(id)
		{
			var url = api_url('social','idol','type=del&id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('取消关注成功',function(){
					$.phpok.reload();
				}).lock();
			});
		},
		black_add:function(id)
		{
			var url = api_url('social','black','type=add&id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('拉黑用户成功',function(){
					$.phpok.reload();
				}).lock();
			});
		},
		black_del:function(id)
		{
			var url = api_url('social','black','type=del&id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('解除黑名单成功',function(){
					$.phpok.reload();
				}).lock();
			});
		},
		homepage:function(obj)
		{
			$(obj).ajaxSubmit({
				'url':api_url('social','homepage'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('数据保存成功');
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
