/**
 * 后台自定义表单中涉及到的JS触发
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年01月18日
**/
;(function($){
	$._configForm = {
		text:function(id,val)
		{
			if(id == 'form_btn'){
				if(val == '' || val == 'undefined'){
					$("#ext_quick_words_html").show();
					$("#ext_color_html").hide();
					return true;
				}
				if(val == 'color'){
					$("#ext_quick_words_html").hide();
					$("#ext_color_html").show();
					return true;
				}
				$("#ext_quick_words_html").hide();
				$("#ext_color_html").hide();
				return true;
			}
			if(id == 'eqt'){
				$("#ext_quick_type").val(val);
			}
		},
	}
})(jQuery);