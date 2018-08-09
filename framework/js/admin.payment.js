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
		}
	}
	$(document).ready(function(){
		if($("#id").length > 0){
			top.$.desktop.title(p_lang('编辑支付方案'));
		}else{
			top.$.desktop.title(p_lang('添加支付方案'));
		}
	});
})(jQuery);

