/**
 * 全局参数动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年07月01日
**/
;(function($){
	$.admin_all = {
		//样式
		setting_style:function(site_id)
		{
			var tpl_id = $("#tpl_id").val();
			var url = get_url('all','tpl_setting','id='+site_id+"&tplid="+tpl_id);
			$.win(p_lang('站点ID {id} 自定义模板设置','#'+site_id),url);
		},
		resetting:function()
		{
			$.dialog.confirm('确定要初始化风格配置吗？',function(){
				var url = get_url('all','tpl_resetting');
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('模板初始化成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.tips(rs.info);
					return false;
				});
				return false;
			});
			return false;
		},
		//保存全局信息
		save:function()
		{
			$("#setting").ajaxSubmit({
				'url':get_url('all','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						layer.msg(p_lang('数据信息保存成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		/**
		 * 随机码
		**/
		rand:function()
		{
			var info = $.phpok.rand(16,'all');
			$("#api_code").val(info);
		},
		rsa_create:function()
		{
			$.dialog.confirm('确定要生成新的证书吗？生成后请修改其他使用到通迅证书的参数',function(){
				var obj = $.dialog.tips('生成中，请稍候…').lock();
				$.phpok.json(get_url('all','rsa'),function(rs){
					obj.close();
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$("#public_key").val(rs.info.public_key);
					$("#private_key").val(rs.info.private_key);
				});
			});
		},
		rsa_create2:function()
		{
			$.dialog.confirm('确定要生成新的证书吗？生成后请修改其他使用到通迅证书的参数',function(){
				var obj = $.dialog.tips('生成中，请稍候…').lock();
				$.phpok.json(get_url('all','rsa'),function(rs){
					obj.close();
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$("#public_key2").val(rs.info.public_key);
					$("#private_key2").val(rs.info.private_key);
				});
			});
		},
		ext_save:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('all','ext_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('数据保存成功'));
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
			if(id && id != 'undefined'){
				var url = get_url('all','gset','id='+id);
				var title = p_lang('维护设置');
			}else{
				var url = get_url('all','gset');
				var title = p_lang('添加全局组');
			}
			$.dialog.open(url,{
				'title':title,
				'width':'70%',
				'height':'331px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_all.group_set();
					return false;
				},
				'okVal':p_lang('保存设置'),
				'cancel':true
			})
		},
		group_set:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('all','gset_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						//刷新父级标签
						var all_seturl = get_url('all');
						var home_url = get_url('index','homepage');
						var id = $("#id").val();
						var this_url = '';
						if(id && parseInt(id)>0){
							this_url = get_url('all','set','id='+id);
						}
						top.$("#LAY_app_tabsheader li").each(function(i){
							var layid = $(this).attr('lay-attr');
							if(layid){
								layid = layid.replace(/\&\_noCache=[0-9\.]+/g,'');
							}
							var chk = webroot+layid;
							if(chk.indexOf(all_seturl) != -1 || chk.indexOf(home_url) != -1){
								top.$('.layadmin-iframe').eq(i)[0].contentWindow.location.reload(true);
							}
							if(this_url && chk.indexOf(this_url) != -1){
								$(this).find("span").text($("#title").val());
							}
						});
						$.dialog.tips(p_lang('保存操作成功'));
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		group_delete:function(id)
		{
			var url = get_url('all','ext_gdelete','id='+id);
			$.dialog.confirm(p_lang('确定要删除此组信息吗？删除后相关数据都会一起被删除'),function(){
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('组删除成功'),function(){
							var all_seturl = get_url('all')+'&_noCache';
							var home_url = get_url('index','homepage');
							var delete_url = get_url('all','set','id='+id);
							top.$("#LAY_app_tabsheader li").each(function(i){
								var layid = $(this).attr('lay-attr');
								var chk = webroot+layid;
								if(chk.indexOf(all_seturl) != -1 || chk.indexOf(home_url) != -1){
									top.$('.layadmin-iframe').eq(i)[0].contentWindow.location.reload(true);
								}
							});
							window.setTimeout(function(){
								top.layui.admin.events.closeThisTabs();
							}, 500);
						});
						return true;
					}
					$.dialog.tips(data.info);
					return false;
				})
			});
		},
		domain_default:function(id)
		{
			var url = get_url("all","domain_default","id="+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('主域名设置成功'),{time:1500},function(){
						$.phpok.reload();
					})
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		},
		domain_add:function()
		{
			var domain = $("#domain_0").val();
			if(!this._domain_check(domain)){
				return false;
			}
			var url = get_url("all","domain_save","domain="+$.str.encode(domain));
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('域名添加成功'),function(){
						$.phpok.reload();
					});
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		domain_update:function(id)
		{
			var domain = $("#domain_"+id).val();
			if(!this._domain_check(domain)){
				return false;
			}
			var url = get_url("all","domain_save","id="+id+"&domain="+$.str.encode(domain));
			$.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('域名更新成功'));
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		},
		domain_delete:function(id)
		{
			layer.confirm(p_lang('确定要删除此域名吗'),function(){
				var url = get_url("all","domain_delete")+"&id="+id;
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		domain_mobile:function(id,act_mobile)
		{
			var url = get_url('all','domain_mobile','act_mobile='+act_mobile+'&id='+id);
			$.phpok.json(url,function(data){
				if(data.status){
					$.phpok.reload();
					return true;
				}
				layer.alert(data.info);
				return false;
			})
		},
		vcode_save:function()
		{
			$('#post_save').ajaxSubmit({
				'url':get_url('all','vcode_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						layer.msg(p_lang('验证码信息配置保存成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		//域名规则测试
		_domain_check:function(domain)
		{
			if(!domain || domain == 'undefined'){
				$.dialog.alert(p_lang('域名不能为空'));
				return false;
			}
			domain = domain.toLowerCase();
			if(domain.substr(0,7) == "http://" || domain.substr(0,8) == "https://"){
				$.dialog.alert(p_lang('域名不能以http://或https://开头'));
				return false;
			}
			var chk = new RegExp('/');
			if(chk.test(domain)){
				$.dialog.alert(p_lang('域名填写不正确'));
				return false;
			}
			return true;
		},
		system:function(obj)
		{
			$(obj).ajaxSubmit({
				'url':get_url('all','system_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('数据保存成功');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	};
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			})
		}
		if($("#_quick_insert").length > 0){
			var url = get_url('ext','select','type=all');
			url += '&module='+$("#_quick_insert").attr('data-id');
			var forbid='';
			$("input[data-name=fields]").each(function(){
				var val = $(this).val();
				if(val){
					if(forbid){
						forbid += ",";
					}
					forbid += val;
				}
			});
			if(forbid){
				url += "&forbid="+$.str.encode(forbid);
			}
			$.phpok.ajax(url,function(data){
				$("#_quick_insert").html(data);
				layui.use('form',function(){
					layui.form.render();
				})
			})
		}
		$(".layui-input").bind("keyup",function(e){
			if(e.keyCode == 13){
				var id = $(this).attr('id');
				$("#"+id+"_submit").click();
			}
		});
    });
})(jQuery);
