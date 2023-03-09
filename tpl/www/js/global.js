/**
 * 公共页脚本
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月14日
**/

/**
 * 导航菜单下拉（由 Bootstrap 自带的点击改成鼠标移过就生效）
**/
function dropdownOpen() {
    var $dropdownLi = $('li.dropdown');
    $dropdownLi.mouseover(function() {
        $(this).addClass('show').find(".dropdown-menu").addClass('show');
    }).mouseout(function() {
        $(this).removeClass('show').find(".dropdown-menu").removeClass('show');
    });
}

/**
 * 搜索框
**/
function top_search(obj)
{
	var title = $(obj).find("input[name=keywords]").val();
	if(!title){
		$.dialog.tips('请输入要搜索的关键字');
		return false;
	}
	return true;
}

// 用户退出
function logout(t)
{
	var tip = '您确定要退出吗？';
	if(t && t != 'undefined'){
		tip = '您好【'+t+'】，确定要退出吗？';
	}
	$.dialog.confirm(tip,function(){
		var url = api_url('logout');
		$.phpok.json(url,function(rs){
			var linkto = window.location.href;
			if(linkto.indexOf('usercp')>-1){
				linkto = webroot;
			}
			$.dialog.tips('成功退出，欢迎您下次登录平台',function(){
				$.phpok.go(linkto);
			}).lock();
		})
	});
}

/**
 * 国家切换，适用于电商
**/
function country_change(id)
{
	var url = api_url('worlds','change','country_id='+id);
	var obj = $.dialog.tips('正在切换国家，请稍候…',100).lock();
	$.phpok.json(url,function(rs){
		obj.close();
		if(!rs.status){
			$.dialog.tips(rs.info);
			return false;
		}
		$.phpok.reload();
	})
}

/**
 * 导航菜单是否跟随
**/
function header_nav_action(pos,e)
{
	if(pos > 40){
		$(".headnav").css("position","fixed").css("top","0");
	}else{
		$(".headnav").css("position","relative");
	}
}


/**
 * 基于Ajax加载内容
**/
var is_loading = false;
var is_locking = false;
function loadmore(id,func)
{
	if(is_loading || is_locking){
		return false;
	}
	var scrollTop = $(this).scrollTop();
	var scrollHeight = $(document).height() -500;
	var windowHeight = $(this).height();
	if (scrollTop + windowHeight >= scrollHeight) {
		is_loading = true;
		var url = window.location.href;
		var data = {};
		data['pageid'] = pageid+1;
		data['ajax'] = 1;
		var loading = $.dialog.tips('加载中，请稍候…',100).lock();
		$.phpok.json(url,function(rs){
			is_loading = false;
			if(!rs.status){
				is_locking = true;
				loading.content('已全部加载…').time(1.5);
				return false;
			}
			loading.close();
			is_locking = false;
			pageid = data['pageid'];
			$("#"+id).append(rs.info);
			if(func && func != 'undefined' && typeof func == 'function'){
				(func)();
			}
		},data);
	}
}


$(document).ready(function(){
    //返回顶部
	if ($("meta[name=toTop]").attr("content") == "true") {
		$("<div id='toTop' class='toTop'><i class='fa fa-arrow-up' style='font-size:20px;'></i></div>").appendTo('body');
		$("#toTop").css({
			width: '40px',
			height: '40px',
			bottom: '10px',
			right: '15px',
			position: 'fixed',
			cursor: 'pointer',
			zIndex: '999999'
		});
		if ($(this).scrollTop() == 0) {
			$("#toTop").hide();
		}
		$(window).scroll(function(event) {
			var top_position = $(this).scrollTop();
			if (top_position == 0) {
				$("#toTop").hide();
			}
			if (top_position != 0) {
				$("#toTop").show();
			}
			//导航菜单执行
			header_nav_action(top_position,event);
		});
		$("#toTop").click(function(event) {
			$("html,body").animate({
				scrollTop: "0px"
			}, 666)
		});
	}

	//评论操作
	if($("#comment-post").length > 0){
	    //提交评论
	    $("#comment-post").submit(function(){
			$.comment.post($("#comment-post")[0]);
			return false;
		});
		if(typeof CKEDITOR != 'undefined'){
			CKEDITOR.on('instanceReady', function(evt) {
				evt.editor.setKeystroke(CKEDITOR.CTRL + 13, 'save');
			});
			CKEDITOR.instances['comment'].on('save', function(event) {
				window.onbeforeunload = null;
				$.comment.post($("#comment-post")[0]);
				return false;
			});
		}
		$(document).keypress(function(e){
			if(e.ctrlKey && e.which == 13 || e.which == 10) {
				$.comment.post($("#comment-post")[0]);
				return false;
			}
		});
	}

	//电商操作
	if(biz_status && biz_status != 'undefined' && biz_status == '1'){
		$.cart.total();
	}

	$(document).off('click.bs.dropdown.data-api');
	dropdownOpen();//调用


	SyntaxHighlighter.config['space'] = "&nbsp;";
	SyntaxHighlighter.config['quick-code'] = false
	SyntaxHighlighter.all();
	$("input[type=text],input[type=password],input[type=email],input[type=tel],select").addClass("form-control");
	$("input[type=checkbox],input[type=radio]").addClass("form-check-input");
});
