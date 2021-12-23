/**
 * 前台页面脚本_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
;(function($){
	$.phpok_app_pm = {
		read:function(id)
		{
			var url = api_url('pm','read','id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$("#unread-"+id).remove();
				return true;
			})
		},
		all:function()
		{
			var url = api_url('pm','all');
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('已更新').lock();
				$.phpok.reload();
				return true;
			})
		}
	}
})(jQuery);
