/**
 * 公共页面JS执行，需要加工 artdialog.css
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年03月17日
**/
function top_search()
{
	var title = $("#top-keywords").val();
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



;(function($){

	/**
	 * 会员相关操作
	**/
	$.user = {
		login: function(title){
			if(!title || title == 'undefined'){
				title = p_lang('会员登录');
			}
			var email = $("#email").val();
			var mobile = $("#mobile").val();
			var url = get_url('login','open');
			if(email){
				url += "&email="+$.str.encode(email);
			}
			if(mobile){
				url += "&mobile="+$.str.encode(mobile);
			}
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'300px',
				'height':'180px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('会员登录'),
				'cancel':true
			});
		},
		register:function()
		{
			//
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
					if(rs.status){
						$.dialog.alert('感谢您提交的评论',function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	};

	/**
	 * 地址薄增删改管理
	**/
	$.address = {
		add:function()
		{
			var width = '500px',height = '500px';
			if($(window).width()<1024){
				width = '90%';
				height = '90%';
			}
			var url = get_url('usercp','address_setting');
			$.dialog.open(url,{
				'title':p_lang('添加新地址'),
				'lock':true,
				'width':width,
				'height':height,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交保存',
				'cancel':true
			})
		},

		edit:function(id)
		{
			var width = '500px',height = '500px';
			if($(window).width()<1024){
				width = '90%';
				height = '90%';
			}
			var url = get_url('usercp','address_setting','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑地址 {id}',"#"+id),
				'lock':true,
				'width':width,
				'height':height,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'保存数据',
				'cancel':true
			});
		},

		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这个地址吗？地址ID {id}',"#"+id),function(){
				var url = api_url('usercp','address_delete','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		},
		set_default:function(id)
		{
			$.dialog.confirm(p_lang('确定要设置这个地址为默认地址吗？地址ID {id}',"#"+id),function(){
				var url = api_url('usercp','address_default','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		},
		address_select:function(id)
		{
			//
		}
	}
})(jQuery);


$(document).ready(function(){
    //返回顶部
    if ($("meta[name=toTop]").attr("content") == "true") {
    	$("<div id='toTop' class='toTop'></div>").appendTo('body');
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
				$.comment.post();
				return false;
			}
		});
	}

});
