/**************************************************************************************************
	文件： js/global.js
	说明： PHPOK默认模板中涉及到的JS
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年9月1日
***************************************************************************************************/
function top_search()
{
	var title = $("#top-keywords").val();
	if(!title)
	{
		alert('请输入要搜索的关键字');
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



;(function($){

	/**
	 * 会员相关操作
	**/
	$.user = {
		login: function(title){
			var url = get_url('login','open');
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'500px',
				'height':'400px'
			});
		},
		logout: function(title){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您确定要退出吗？',function(){
				$.phpok.go(get_url('logout'));
			});
		}
	};

	/**
	 * 评论相关操作
	**/
	$.comment = {
		post:function()
		{
			$("#comment-post").ajaxSubmit({
				'url':api_url('comment','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status == 'ok'){
						$.dialog.alert('感谢您提交的评论',function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);


function fav_add(id,obj)
{
	var val = ($(obj).val()).trim();
	if(val == '已收藏'){
		$.dialog.alert('已收藏过，不能重复执行');
		return false;
	}
	var url = api_url('fav','add','id='+id);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			$(obj).val('加入收藏成功');
			window.setTimeout(function(){
				$(obj).val('已收藏')
			}, 1000);
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

$(document).ready(function(){
    //返回顶部
    if ($("meta[name=toTop]").attr("content") == "true") {
    	$("<div id='toTop'><img src='../images/to-top.png'></div>").appendTo('body');
    	$("#toTop").css({
    		width: '50px',
    		height: '50px',
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


	if($("#comment-post").length > 0){
	    //提交评论
	    $("#comment-post").submit(function(){
			$.comment.post();
			return false;
		});
		$(document).keypress(function(e){
			if(e.ctrlKey && e.which == 13 || e.which == 10) {
				save_comment();
				return false;
			}
		});
	}

});
