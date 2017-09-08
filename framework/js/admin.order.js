/**
 * 后台订单管理相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月07日
**/
;(function($){
	$.admin_order = {
		address:function()
		{
			var uid = $("#user_id").val();
			var url = get_url('address','open','tpl=address_order');
			if(uid){
				url = get_url('address','open','tpl=address_order&type=user_id&keywords='+uid);
			}
			$.dialog.open(url,{
				'title':p_lang('选择收件人地址'),
				'lock':true,
				'width':'800px',
				'height':'600px'
			});
		}
	}
})(jQuery);