/**
 * 后台首页涉及到的样式
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年08月13日
 **/
;
(function ($) {
	$.admin_index = {
		site: function () {
			$.dialog.open(get_url('site', 'add'), {
				'title': p_lang('添加站点'),
				'lock': true,
				'width': '450px',
				'height': '150px',
				'resize': false,
				'ok': function () {
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal': p_lang('添加新站点'),
				'cancel': true
			});
		},
		me: function () {
			$.dialog.open(get_url("me", "setting"), {
				"title": p_lang('修改管理员信息'),
				"width": 600,
				"height": 217,
				"lock": true,
				'move': false,
				'ok': function () {
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_me.setting_submit();
					return false;
				},
				'okVal': p_lang('提交保存'),
				'cancel': true
			});
		},
		pass: function () {
			$.dialog.open(get_url("me", "pass"), {
				"title": p_lang('管理员密码修改'),
				"width": 600,
				"height": 260,
				"lock": true,
				'move': false,
				'ok': function () {
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_me.pass_submit();
					return false;
				},
				'okVal': p_lang('提交保存'),
				'cancel': true
			});
		},
		logout: function () {
			$.dialog.confirm(p_lang('您确定要退出吗？'), function () {
				$.phpok.go(get_url("logout"));
			});
		},
		clear: function (type,obj) {
			if(!type || type == 'undefined'){
				$.phpok.json(get_url('index','clear','type=all'),function(rs){
					if(rs.status){
						$.dialog.tips('缓存清空完成');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
				return true;
			}
			$.phpok.json(get_url('index','clear','type='+type),function(rs){
				if(rs.status){
					$.dialog.tips('清理完成');
					if(type != 'all'){
						$(obj).val('清理完成').addClass("layui-btn-disabled");
					}else{
						$("input[data-name=cache]").val('清理完成').addClass("layui-btn-disabled");
					}
					layui.form.render();
					return true;
				}
				$.dialog.alert(rs.info);
				return true;
			});
		},
		lang: function (val) {
			$.phpok.go(get_url("index", '', '_langid=' + val));
		},
		pendding: function () {
			$.phpok.json(get_url('index', 'pendding'), function (rs) {
				$("span.layui-badge").remove();
				$.cookie.del('badge');
				if (rs.status && rs.info) {
					var list = rs.info;
					var html = '<span class="layui-badge-dot" style="margin-left:2px;"></span>'
					var total = 0;
					var pid_info = '';
					for (var key in list) {
						if (key == 'update_action') {
							$.admin_index.update();
						} else {
							if (list[key]['id'] == 'user' || list[key]['id'] == 'reply' || list[key]['id'] == 'update') {
								$("li[data-name=" + list[key]['id'] + "] a,dd[data-name=" + list[key]['id'] + "] a").append(html.replace('{total}', list[key]['total']));
							} else {
								if (pid_info) {
									pid_info += ",";
								}
								pid_info += list[key]['id'] + ":" + list[key]['total'];
								total = parseInt(total) + parseInt(list[key]['total']);
								$("dd[pid=" + list[key]['id'] + "] a").append(html.replace('{total}', list[key]['total']));
							}
						}
					}
					if (pid_info != '') {
						$.cookie.set('badge', pid_info);
					}

					if (total > 0) {
						$("li[data-name=list] a").eq(0).append(html.replace('{total}', total));
					}
				}
				$.phpok.message('badge', true);
			});
			var chk = $.cookie.get("notout");
			//每五分钟执行一次
			if(chk && chk != 'undefined'){
				var that = this;
				setTimeout(function(){
					that.pendding();
				}, 300000);
			}
		},
		update: function () {
			$.phpok.json(get_url('update', 'check'), function (data) {
				if (data.status == 'ok') {
					$.dialog.notice({
						title: '友情提示',
						width: 220, // 必须指定一个像素宽度值或者百分比，否则浏览器窗口改变可能导致artDialog收缩
						content: '您的程序有新的更新，为了保证系统安全，建议您及时更新程序',
						icon: 'update',
						time: 10
					});
				}
			});
		},
		develop: function (val) {
			if (val == 1) {
				$.dialog.tips(p_lang('正在切换到开发模式，请稍候…'));
				$.phpok.json(get_url('index', 'develop', 'val=1'), function (data) {
					if (data.status) {
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			} else {
				$.dialog.tips(p_lang('正在切换到应用模式，请稍候…'));
				$.phpok.json(get_url('index', 'develop', 'val=0'), function (data) {
					if (data.status) {
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			}
		},
		quick_link: function (id)
		{
			var url = get_url('index','qlink');
			var tit = p_lang('自定义快捷链接');
			if(id){
				url += "&id="+id;
				tit = p_lang('编辑链接');
			}
			$.dialog.open(url, {
				'title': tit,
				'lock': true,
				'width': '750px',
				'height': '370px',
				'ok': function () {
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		quick_link_delete:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这个快捷链接吗？'),function(){
				var url = get_url('index','qlink_delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('链接删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			})
		},
		icolist:function()
		{
			$.dialog.open(get_url('project','icolist'),{
				'title':p_lang('选择图标'),
				'lock':true,
				'width':'700px',
				'height':'60%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交',
				'cancel':true
			})
		},
		editor_resinfo:function()
		{
			$.win(p_lang('批量更新编辑框附件'),get_url('res','editor_update_all'));
		},
		layui_refresh:function()
		{
			var layid = $("#LAY_app_tabsheader li.layui-this").attr("lay-id");
			var obj = $(".layui-body").find('.layui-show').find('iframe');
			obj[0].contentWindow.layui.use('form',function(){
				var form = obj[0].contentWindow.layui.form;
				form.render();
				$.dialog.tips('刷新渲染成功');
			})
		},
		copyright:function()
		{
			var t = $.dialog.tips('正在检测中，请稍候…',100).lock();
			$.phpok.json(api_url('index','copyright'),function(rs){
				t.close();
				if(!rs.status){
					$.dialog.alert(rs.info,true,'error').title('友情提示');
					return false;
				}
				$.dialog.alert(rs.info,true,'succeed').title('商业授权');
				return true;
			})
		},
		set_copyright:function()
		{
			$.dialog({
				'title':p_lang('授权变更，仅支持一次修改'),
				'content':document.getElementById("copyright-license-change"),
				'lock':true,
				'cancel':true,
				'okVal':'提交保存',
				'ok':function(){
					var vtype = $("input[name=v-type]:checked").val();
					if(vtype == 'LGPL'){
						$.dialog.alert('LGPL授权不需要修改');
						return false;
					}
					var vcompany = $("input[name=v-company]").val();
					if(!vcompany){
						$.dialog.alert('授权企业不能为空');
						return false;
					}
					var vdomain = $("input[name=v-domain]").val();
					if(!vdomain){
						$.dialog.alert('授权域名不能为空');
						return false;
					}
					var vcode = $("input[name=v-code]").val();
					if(!vcode){
						$.dialog.alert('授权码不能为空');
						return false;
					}
					var date = $("input[name=v-date]").val();
					var t = vtype == 'PBIZ' ? '个人授权' : '企业授权';
					$.dialog.confirm('您的授权信息是：<br/>授权企业：<span style="color:red">'+vcompany+'</span><br/>授权类型：<span style="color:red">'+t+'</span><br/>授权域名：<span style="color:red">'+vdomain+'</span><br/>授权代码：<span style="color:red">'+vcode+'</span><br/>请检查是否正确，一经确认不支持再修改',function(){
						$.phpok.json(get_url('index','copyright'),function(rs){
							if(!rs.status){
								$.dialog.alert(rs.info);
								return false;
							}
							$.dialog.tips(p_lang('授权变更成功'),function(){
								top.$.phpok.reload();
							}).lock();
						},{
							'type':vtype,
							'domain':vdomain,
							'code':vcode,
							'company':vcompany,
							'date':date
						});
					});
					return false;
				}
			})
		},
		download_table:function(){
			$.dialog.confirm('确定要从远程中更新码表信息吗？',function(){
				var url = get_url('index','download_table');
				var t = $.dialog.tips('正在下载中，请稍候…',100000).lock();
				$.phpok.json(url,function(rs){
					if(!rs.status){
						t.content(rs.info).time(2);
						return false;
					}
					t.content('下载成功，请重新检测').time(2);
				});
			});
		},
		checking:function()
		{
			var self = this;
			var t = $("#rslist").html();
			if(t.length>10){
				var title = "确定要重新开始检测吗？系统会清除当前检测结果";
			}else{
				var title = "确定开始检测吗？检测过程中请不要关闭浏览器";
			}
			$.dialog.confirm(title,function(){
				$("#folderlist").html("");
				$("#total_html").hide();
				$("#rslist").html('');
				var obj_tip = $.dialog.tips("正在检测中，请稍候…",10000000);
				self.loading(obj_tip);
			});
		},
		loading:function(obj)
		{
			var self = this;
			var url = get_url("index","getlist");
			var t = $("#folderlist").html();
			if(t){
				var list = t.split(",");
				var folder = list[0];
				url += "&folder="+$.str.encode(folder);
				obj.content("正在检查文件夹："+folder);
				list.shift();
			}else{
				var list = new Array();
				obj.content("正在检查根目录");
			}
			$.phpok.json(url,function(rs){
				if(!rs.status){
					obj.content(rs.info).time(2);
					return false;
				}
				var info = rs.info;
				var total = $("#total").html();
				if(!total || total == 'undefined'){
					total = 0;
				}
				total = parseInt(total);
				var ntotal = total + parseInt(info.total);
				$("#total").html(ntotal);
				$("#total_html").show();
				if(info.dirlist){
					list = list.concat(info.dirlist);
				}
				$("#folderlist").html(list.join(","));
				//文件列表
				if(info.rslist){
					var html = template('art-tpl', {rslist: info.rslist});
					$("#rslist").append(html);
				}
				if(list.length>0){
					self.loading(obj);
				}else{
					obj.content("检测完成，请查阅").time(2);
				}
				return true;
			});
		},
		clear_ignore:function()
		{
			$.dialog.confirm("确定要清除手工忽略的文件吗？",function(){
				$.phpok.json(get_url('index','clear_ignore'),function(rs){
					if(rs.status){
						$.dialog.tips("已删除忽略文件").lock();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		ignore:function(id)
		{
			var url = get_url("index",'ignore','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$("#id-"+id).remove();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		notin:function(type)
		{
			if(!type){
				$.dialog.alert('未指定要忽略的类型');
				return false;
			}
			var list = type.split(",");
			for(var i in list){
				$("tr[data-ext="+list[i]+"]").remove();
			}
			return true;
		},
		showgroup:function(id,title)
		{
			$.dialog({
				"title":title,
				"content":document.getElementById("groups-"+id),
				"lock":true
			});
		}
	}
})(jQuery);


$(document).ready(function () {
	//监听事件
	document.addEventListener("keydown", function (e) {
		if (e.keyCode == 116) {
			e.preventDefault();
			$('a[layadmin-event=refresh]').click();
			//要做的其他事情
		}
	}, false);
	window.addEventListener("message", function (e) {
		if (e.origin != window.location.origin) {
			return false;
		}
		if (e.data == 'close') {
			$('.aui_close').click();
			return true;
		}
		if (e.data == 'pendding') {
			$.admin_index.pendding();
		}
	}, false);
	$.admin_index.pendding();

	//自定义右键
	var r_menu = [
		[{
			'text': p_lang('刷新网页'),
			'func': function () {
				$.phpok.reload();
			}
		}, {
			'text': p_lang('清空缓存'),
			'func': function () {
				$.admin_index.clear();
			}
		}, {
			'text': p_lang('修改我的信息'),
			'func': function () {
				$.admin_index.me();
			}
		}],
		[{
			'text': p_lang('关于PHPOK'),
			'func': function () {
				$("a[layadmin-event=about]").click();
			}
		}]
	];
	$(window).smartMenu(r_menu, {
		'textLimit': 8
	});
});