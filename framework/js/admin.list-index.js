/**
 * 内容首页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月11日
**/
;(function($){
	$(document).ready(function(){
		$("#project li").mouseover(function(){
			$(this).addClass("hover");
		}).mouseout(function(){
			$(this).removeClass("hover");
		}).click(function(){
			var url = $(this).attr("href");
			var txt = $(this).find('.txt').text();
			if(url){
				$.win(txt,url);
				return true;
			}
			$.dialog.alert(p_lang('未指定动作'));
			return false;
		});
		window.addEventListener("message",function(e){
			if(e.origin != window.location.origin){
				return false;
			}
			if(e.data == 'badge'){
				$.admin.badge();
				return true;
			}
		}, false);
		$.admin.badge();
	});
})(jQuery);