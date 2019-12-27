/**
 * 后台首页涉及到的样式
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
						$.dialog.alert('缓存清空完成',true,'succeed');
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
					//var html = '<span class="layui-badge">{total}</span>';
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
				'height': '272px',
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