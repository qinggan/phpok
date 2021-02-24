/**
 * 后面页面脚本_用于过滤敏感的，粗爆的字词，一行一个，用户提交表单数据时直接报错
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年09月04日 15时50分
**/
;(function($){
	$.fn.extend({
		insertAt: function(myValue){
			var $t=$(this)[0];
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			}
			else
			if ($t.selectionStart || $t.selectionStart == '0') {
				var startPos = $t.selectionStart;
				var endPos = $t.selectionEnd;
				var scrollTop = $t.scrollTop;
				$t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
				this.focus();
				$t.selectionStart = startPos + myValue.length;
				$t.selectionEnd = startPos + myValue.length;
				$t.scrollTop = scrollTop;
			}
			else {
				this.value += myValue;
				this.focus();
			}
		}
	});
})(jQuery);

;(function($){
	$.admin_dirtywords = {
		save:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('dirtywords','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('配置保存成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		setting:function(obj)
		{
			$(obj).ajaxSubmit({
				'url':get_url('dirtywords','setting_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('配置保存成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		autoHeight:function(element)
		{
			$(element).css({
				'height': 'auto',
				'overflow-y': 'hidden'
			}).height(element.scrollHeight);
		}
	}
})(jQuery);

$(document).ready(function(){
	$('textarea#content').each(function() {
		$.admin_dirtywords.autoHeight(this);
	});
});
