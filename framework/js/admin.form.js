/**
 * 表单页面涉及到的一些信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年01月21日
**/
;(function($){
	$.admin_form = {
		view:function(id,pid){
			var url = get_url('form','preview','id='+id+"&pid="+pid);
			$.dialog.open(url,{
				'title':p_lang('预览'),
				'lock':true,
				'width':'750px',
				'height':'650px',
				'ok':true
			});
		}
	}
})(jQuery);