;(function($){
	$.framework = {
		menu:function()
		{
			if($(".layout-left").is(":hidden")){
				$(".layout-left").removeClass("layout-close").show().animate({"margin-left":'0px'},"fast",function(){
					$(".layout-main").removeClass("main-full");
				});
			}else{
				$(".layout-main").addClass("main-full");
				$(".layout-left").animate({"margin-left":'-200px'},"fast",function(){
					$(this).hide();
					$(this).addClass("layout-close");
				});
			}
		},
		left_height()
		{
			var my_height = $('.layout-left').height();
			var win_h = $(window).height();
			var doc_h = $('body').height();
			var t = doc_h>win_h ? doc_h : win_h;
			if(t>my_height){
				$(".layout-left").css("height",t+"px");
			}
		},
		auto_highlight()
		{
			var _link = '.nk-menu-link, .menu-link, .nav-link',
				_currentURL = window.location.href,
				fileName = _currentURL.substring(0, _currentURL.indexOf("#") == -1 ? _currentURL.length : _currentURL.indexOf("#")),
				fileName = _currentURL.substring(0, _currentURL.indexOf("_noCache") == -1 ? _currentURL.length : _currentURL.indexOf("_noCache"));
			if(fileName.substr(-1) == '&'){
				fileName = fileName.substr(0,fileName.length-1);
			}
			$(_link).each(function() {
				var self = $(this),
					_self_link = self.attr('href');
					_self_link = _self_link.substring(0, _self_link.indexOf("#") == -1 ? _self_link.length : _self_link.indexOf("#")),
					_self_link = _self_link.substring(0, _self_link.indexOf("_noCache") == -1 ? _self_link.length : _self_link.indexOf("_noCache"));
					if(_self_link.substr(-1) == '&'){
						_self_link = _self_link.substr(0,_self_link.length-1);
					}
				if (_self_link == fileName) {
					self.closest("li").addClass('active current-page').parents().closest("li").addClass("active current-page");
					self.closest("li").children('.menu-sub').css('display', 'block');
					self.parents().closest("li").children('.menu-sub').css('display', 'block');
				} else {
					self.closest("li").removeClass('active current-page').parents().closest("li:not(.current-page)").removeClass("active");
				}
			});
		}
	}
	$(document).ready(function(){
		$.framework.auto_highlight(); //自动高亮及打开当前页面
		if($(".layout-left").is(":hidden")){
			$(".layout-main").addClass("main-full");
			$(".layout-left").animate({"margin-left":'-200px'},"fast",function(){
				$(this).hide();
				$(this).addClass("layout-close");
			});
		}else{
			$(".layout-left").removeClass("layout-close").show().animate({"margin-left":'0px'},"fast",function(){
				$(".layout-main").removeClass("main-full");
			});
		}
		$('.menu-toggle').click(function(e){
			if($(this).hasClass("active")){
				$(this).closest("li").children('.menu-sub').hide();
				$(this).removeClass("active");
				$(this).parent().removeClass("active");
			}else{
				$(this).addClass("active");
				$(this).closest("li").children('.menu-sub').show();
				$(this).parent().addClass('active');
			}
			e.preventDefault();
		})
	});
})(jQuery);