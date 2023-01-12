/**
 * 公共页面JS执行，需要加工 artdialog.css
 * @作者 qinggan <admin@phpok.com>
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2021年5月14日
**/

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

	$.vcode = {
		/**
		 * 短信发送
		 * @参数 obj 按钮 input 对象，一般直接使用this
		 * @参数 fid 手机号文本框ID，主要用于获取手机号码
		 * @参数 act 动作参数，register 注册，login 登录
		**/
		sms:function(obj,fid,act)
		{
			if(!act || act == 'undefined'){
				act = 'login';
			}
			if(fid.substr(0,1) == '.' || fid.substr(0,1) == '#'){
				var val = $(fid).val();
			}else{
				var val = $("#"+fid).val();
			}
			if(!val){
				$.dialog.tips('手机号不能为空').lock();
				return false;
			}
			this._action('sms',act,val,obj);
		},

		/**
		 * 邮件验证码发送
		 * @参数 obj 按钮 input 对象，一般直接使用this
		 * @参数 fid 邮箱文本框ID，主要用于获取邮箱号码
		 * @参数 act 动作参数，register 注册，login 登录
		**/
		email:function(obj,fid,act)
		{
			if(!act || act == 'undefined'){
				act = 'login';
			}
			if(fid.substr(0,1) == '.' || fid.substr(0,1) == '#'){
				var val = $(fid).val();
			}else{
				var val = $("#"+fid).val();
			}
			if(!val){
				$.dialog.tips('邮箱不能为空').lock();
				return false;
			}
			this._action('email',act,val,obj);
		},
		_action:function(type,act,val,obj)
		{
			if(time_lock){
				$.dialog.tips('验证码已发送，请稍候…').lock();
				return false;
			}
			var url = api_url('vcode',type,'act='+act);
			if(type == 'sms'){
				url += "&mobile="+val;
			}else{
				url += "&email="+$.str.encode(val);
			}
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info).lock();
					return false;
				}
				time = 60;
				time_lock = true;
				timeout_obj = window.setInterval(function(){
					countdown(obj);
				},1000);
				return true;
			});
		}
	}

	$.login = {
		/**
		 * 弹出小窗口登录（仅支持账号密码登录）
		 * 注意，弹窗的小窗口里的 form onsubmit 事件必须返回 false，不然可能会失败
		**/
		open:function()
		{
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
				'title':p_lang('用户登录'),
				'lock':true,
				'width':'400px',
				'height':'200px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					console.log('147258');
					iframe.$.login.ok(iframe.$("form")[0],true);
					return false;
				},
				'okVal':p_lang('确认登录'),
				'cancel':true
			});
		},
		/**
		 * 短信登录，表单参数有三个：mobile，_vcode（短信验证码），_chkcode（图形验证码，如果有开启）
		 * @参数 obj 当前 form 对象
		**/
		sms:function(obj)
		{
			return this._action(obj,'sms');
		},
		/**
		 * 邮箱验证码登录，表单参数有三个：email，_vcode（邮箱验证码），_chkcode（图形验证码，如果有开启）
		 * @参数 obj 当前 form 对象
		**/
		email:function(obj)
		{
			return this._action(obj,'email');
		},
		/**
		 * 常规基于账号密码登录，表单参数有三个：user（账号），pass（密码），_chkcode（图形验证码，如果有开启）
		 * @参数 obj 当前 form 对象
		**/
		ok:function(obj,isopen)
		{
			if(isopen && isopen != 'undefined'){
				return this._action(obj,'open');
			}
			return this._action(obj,'default');
		},
		getpass:function(id)
		{
			$.phpok.go(get_url('login','getpass','type_id='+id));
		},
		repass:function(obj)
		{
			$(obj).ajaxSubmit({
				url:api_url('login','repass'),
				type:'post',
				dataType:'json',
				success:function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('密码修改成功，请登录'),function(){
							$.phpok.go(get_url('login'));
							return true;
						},'succeed');
						return true;
					}
					if(!rs.info){
						rs.info = p_lang('获取失败，请联系管理员。');
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		_action:function(obj,type)
		{
			if(type == 'sms' || type == 'email'){
				var url = api_url('login',type);
			}else{
				var url = api_url('login');
			}
			$(obj).ajaxSubmit({
				'url':url,
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						$("input[name=_chkcode]").val('');
						$("#vcode").phpok_vcode();
						return false;
					}
					$.dialog.tips('用户登录成功').lock();
					if(type == 'open'){
						top.$.phpok.reload();
						return true;
					}
					if(!rs.url || rs.url == 'undefined'){
						rs.url = $("#_back").val();
						if(!rs.url){
							rs.url = webroot;
						}
					}
					$.phpok.go(rs.url);
					return false;
				}
			});
			return false;
		}
	}

	/**
	 * 用户相关操作
	**/
	/**
	 * 用户相关操作
	**/
	$.user = {
		/**
		 * 用户退出操作
		**/
		logout: function(name){
			var tip = '您确定要退出吗？';
			if(name && name != 'undefined'){
				tip = '您（<i style="color:red;">'+name+'</i>）确定要退出吗？';
			}
			$.dialog.confirm(tip,function(){
				$.phpok.json(api_url('logout'),function(rs){
					$.dialog.tips('成功退出，欢迎您再次登录',function(){
						$.phpok.go(webroot);
					}).lock();
				});
			});
		},

		//修改个人信息事件
		info:function(obj)
		{
			$(obj).ajaxSubmit({
				type:'post',
				url: api_url('usercp','info'),
				dataType:'json',
				success: function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.alert(p_lang('您的信息更新成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
			});
			return false;
		},
		mobile:function(obj)
		{
			$(obj).ajaxSubmit({
				type:'post',
				url: api_url('usercp','mobile'),
				dataType:'json',
				success: function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.alert(p_lang('您的手机号更新成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
			});
			return false;
		},
		pass:function(obj)
		{
			$(obj).ajaxSubmit({
				type:'post',
				url: api_url('usercp','passwd'),
				dataType:'json',
				success: function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.alert(p_lang('您的密码更新成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
			});
			return false;
		},
		vcode:function()
		{
			$.dialog.confirm(p_lang('确定要更新邀请码吗？更新后旧的邀请码就会失败'),function(){
				$.phpok.json(api_url('usercp','vcode'),function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('邀请码更新成功'),function(){
						$.phpok.reload();
					}).lock().time(1);
				})
			});
		}
	};

	$.register = {
		save:function(obj)
		{
			if(!$('input[name=is_ok]').prop('checked')){
				$.dialog.alert('注册前请先同意本站协议');
				return false;
			}
			$(obj).ajaxSubmit({
				'url':api_url('register','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('用户注册成功').lock();
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
		post:function(obj)
		{
			$(obj).ajaxSubmit({
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
				'height':'550px',
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
				var url = api_url('address','delete','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		},
		set_default:function(id)
		{
			$.dialog.confirm(p_lang('确定要设置这个地址为默认地址吗？地址ID {id}',"#"+id),function(){
				var url = api_url('address','default','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		},
		glist:function()
		{
			var that = this;
			var country = $("#country").val();
			var province = $("#province").val();
			var city = $("#city").val();
			var county = $("#county").val();
			var url = api_url('worlds','glist');
			if(country){
				url += "&country="+$.str.encode(country);
			}
			if(province){
				url += "&province="+$.str.encode(province);
			}
			if(city){
				url += "&city="+$.str.encode(city);
			}
			if(county){
				url += "&county="+$.str.encode(county);
			}
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				var html = that._country_html(rs.info.country,country);
				$("#country").html(html).show();
				if(rs.info.province && rs.info.province != 'undefined'){
					$("#province").html(that._pca_html(rs.info.province,province)).show();
					if(rs.info.city && rs.info.city != 'undefined'){
						$("#city").html(that._pca_html(rs.info.city,city)).show();
						if(rs.info.county && rs.info.county != 'undefined'){
							$("#county").html(that._pca_html(rs.info.county,county)).show();
						}else{
							$("#county").html('').hide();
						}
					}else{
						$("#city,#county").html('').hide();
					}
				}else{
					$("#province,#city,#county").html('').hide();
				}
			})
		},
		_country_html:function(glist,val)
		{
			var html = '<option value="">请选择…</option>';
			for(var i in glist){
				html += '<optgroup label="'+glist[i].title+'">';
				var tmplist = glist[i].rslist;
				for(var t in tmplist){
					html += '<option value="'+tmplist[t].title+'"';
					if(val && val != 'undefined' && val == tmplist[t].title){
						html += ' selected';
					}
					html += '>'+tmplist[t].title+'</option>';
				}
				html += '</optgroup>';
			}
			return html;
		},
		_pca_html:function(rslist,val)
		{
			var html = '<option value="">请选择…</option>';
			for(var i in rslist){
				html += '<option value="'+rslist[i].title+'"';
				if(val && val != 'undefined' && val == rslist[i].title){
					html += ' selected';
				}
				html += '>'+rslist[i].title+'</option>';
			}
			return html;
		}
	}

	$.order = {
		payment:function(obj)
		{
			var opener = $.dialog.opener;
			$(obj).ajaxSubmit({
				'url':api_url('payment','create'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					if(!rs.info){
						$.dialog.tips(p_lang('付款成功，请稍候…')).lock();
						//跳转到订单详情
						var sn = $("#order-sn").val();
						var passwd = $("#order-passwd").val();
						$.phpok.go(get_url('order','info','sn='+sn+"&passwd="+passwd));
						return false;
					}
					$.phpok.go(get_url('payment','action','id='+rs.info));
					return false;
				}
			});
			return false;
		}
	}

	$.common = {
		aim:function(id)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			$("html, body").animate({
				scrollTop: $(id).offset().top
			},{
				duration: 500,
				easing: "swing"
			});
		},
		res_delete:function(id)
		{
			$.dialog.confirm('确定要删除媒体资源 #'+id+" 吗？删除后不能恢复",function(){
				$.phpok.json(api_url('res','delete','id='+id),function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips('删除成功');
					$.phpok.reload();
				});
			});
		}
	}
})(jQuery);