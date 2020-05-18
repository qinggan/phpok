/**
 * 后面页面脚本_用于配置微信各种参数信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月24日 20时22分
**/
;(function($){
	var lock;
	$.admin_wxconfig = {
		save:function()
		{
			lock = $.dialog.tips(p_lang('正在保存中，请稍候…'),100).lock();
			$("#post_save").ajaxSubmit({
				'url':get_url('wxconfig','save'),
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
		}
	}
})(jQuery);
