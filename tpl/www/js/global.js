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
	var timeout_obj = null;
	var time = 60;
	var time_lock = false;

	function countdown(obj)
	{
		time--;
		if(time < 1){
			$(obj).val('发送验证码');
			time_lock = false;
			time = 60;
			window.clearInterval(timeout_obj);
			return true;
		}
		var tips = "已发送("+time+")";
		$(obj).val(tips);
	}

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
		login_ok:function(formid){
			if(!formid || formid == 'undefined'){
				formid = 'login-submit';
			}
			$("#"+formid).ajaxSubmit({
				'url':api_url('login','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('会员登录成功');
						if(!rs.url || rs.url == 'undefined'){
							rs.url = $("#_back").val();
							if(!rs.url){
								rs.url = webroot;
							}
						}
						$.phpok.go(rs.url);
						return false;
					}
					$.dialog.alert(rs.info,false,'error');
					return false;
				}
			});
			return false;
		},
		register:function(reg_type)
		{
			//
		},
		logout: function(title){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您确定要退出吗？',function(){
				$.phpok.json(api_url('logout'),function(rs){
					$.phpok.go(webroot);
				});
			});
		}
	};

	$.register = {
		email_code:function(email_id,tpl_id,obj)
		{
			if(time_lock){
				$.dialog.tips('验证码已发送，请稍候…');
				return false;
			}
			var url = api_url('vcode','email','act=register');
			if(tpl_id && tpl_id != 'undefined'){
				url += '&tplid='+tpl_id;
			}
			var email = $("#"+email_id).val();
			if(email){
				url += "&email="+$.str.encode(email);
			}
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				time = 60;
				time_lock = true;
				timeout_obj = window.setInterval(function(){
					countdown(obj);
				},1000);
				return true;
			});
		},
		sms_code:function(mobile_id,tpl_id,obj)
		{
			if(time_lock){
				$.dialog.tips('验证码已发送，请稍候…');
				return false;
			}
			var url = api_url('vcode','sms','act=register');
			if(tpl_id && tpl_id != 'undefined'){
				url += '&tplid='+tpl_id;
			}
			var mobile = $("#"+mobile_id).val();
			if(mobile){
				url += "&mobile="+$.str.encode(mobile);
			}
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				time = 60;
				time_lock = true;
				timeout_obj = window.setInterval(function(){
					countdown(obj);
				},1000);
				return true;
			});
		},
		save:function(id)
		{
			if(!$('#is_ok').prop('checked')){
				$.dialog.alert('注册前请先同意本站协议');
				return false;
			}
			$("#"+id).ajaxSubmit({
				'url':api_url('register','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('会员注册成功').lock();
						var url = $("#_back").val();
						if(!url){
							url = webroot;
						}
						$.phpok.go(url);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		group:function(id)
		{
			$.phpok.go(get_url('register','','group_id='+id));
		}
	}

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
			var url = get_url('usercp','address_setting');
			$.dialog.open(url,{
				'title':p_lang('添加新地址'),
				'lock':true,
				'width':'500px',
				'height':'500px',
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
			var url = get_url('usercp','address_setting','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑地址 {id}',"#"+id),
				'lock':true,
				'width':'500px',
				'height':'500px',
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

	$(".floatbar .weixin").hover(function(){
		var src = $(this).find(".wxpic").attr("data-filename");
		var html = '<img src="'+src+'" border="0" />';
		$(this).find('.wxpic').html(html).show();
	},function(){
		$(this).find('.wxpic').hide();
	});

	//异步定时通知
	window.setTimeout(function(){
		$.phpok.json(api_url('task'),true);
	}, 800);

	if(biz_status && biz_status != 'undefined' && biz_status == '1'){
		$.cart.total();
	}
});
