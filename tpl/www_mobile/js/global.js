/**
 * 公共页脚本
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月14日
**/

function top_search(obj)
{
	var title = $(obj).find("input[name=keywords]").val();
	if(!title){
		$.dialog.alert('请输入要搜索的关键字');
		return false;
	}
	return true;
}

// 退出
function logout(t)
{
	var q = confirm("您好，【"+t+"】，确定要退出吗？");
	if(q == '0')
	{
		return false;
	}
	$.phpok.go(get_url('logout'));
}

function country_change(id)
{
	var url = api_url('worlds','change','country_id='+id);
	var obj = $.dialog.tips('正在切换国家，请稍候…',100).lock();
	$.phpok.json(url,function(rs){
		obj.close();
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		$.phpok.reload();
	})
}

$(document).ready(function(){
    //返回顶部
	if ($("meta[name=toTop]").attr("content") == "true") {
		$("<div id='toTop' class='toTop'></div>").appendTo('body');
		$("#toTop").css({
			width: '50px',
			height: '50px',
			bottom: '80px',
			right: '15px',
			position: 'fixed',
			cursor: 'pointer',
			zIndex: '99999'
		});
		if ($(this).scrollTop() == 0) {
			$("#toTop").hide();
		}
		$(window).scroll(function(event) {
			if ($(this).scrollTop() == 0) {
				$("#toTop").hide();
			}
			if ($(this).scrollTop() != 0) {
				$("#toTop").show();
			}
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

	//客服操作
	$(".floatbar .weixin").hover(function(){
		var src = $(this).find(".wxpic").attr("data-filename");
		var html = '<img src="'+src+'" border="0" />';
		$(this).find('.wxpic').html(html).show();
	},function(){
		$(this).find('.wxpic').hide();
	});

	//电商操作
	if(biz_status && biz_status != 'undefined' && biz_status == '1'){
		$.cart.total();
	}

	//手机版菜单操作
	$("#menu-toggle, .sidebar-bg").click(function(e) {
		e.preventDefault();
		$(".sidebar").toggleClass("active");
		$(".sidebar-bg").toggleClass("active");
	});

	SyntaxHighlighter.config['space'] = "&nbsp;";
	SyntaxHighlighter.config['quick-code'] = false
	SyntaxHighlighter.all();
	$("input[type=text],input[type=password],input[type=email],input[type=tel],select").addClass("form-control");
	$("input[type=checkbox],input[type=radio]").addClass("form-check-input");
});
