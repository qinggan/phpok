/**
 * 后台订单管理相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月07日
 **/
;(function ($) {
	$.admin_order = {
		address: function (type) {
			var uid = $("#user_id").val();
			var url = get_url('address', 'open', 'tpl=address_order&types=' + type);
			if (uid) {
				url = get_url('address', 'open', 'tpl=address_order&type=user_id&keywords=' + uid + "&types=" + type);
			}
			$.dialog.open(url, {
				'title': p_lang('选择收件人地址'),
				'lock': true,
				'width': '800px',
				'height': '600px'
			});
		},
		sn: function () {
			var res = 'KF';
			var myDate = new Date();
			res += myDate.getFullYear();
			var month = myDate.getMonth() + 1;
			if (month.length == 1) {
				month = '0' + month.toString();
			}
			res += month;
			var date = myDate.getDate();
			if (date.length == 1) {
				date = '0' + date.toString();
			}
			res += date;
			var hour = myDate.getHours() + 1;
			if (hour.length == 1) {
				hour = '0' + hour.toString();
			}
			res += hour;
			var minutes = myDate.getMinutes();
			if (minutes.length == 1) {
				minutes = '0' + minutes.toString();
			}
			res += minutes;
			var seconds = myDate.getSeconds();
			if (seconds.length == 1) {
				seconds = '0' + seconds.toString();
			}
			res += seconds;
			var chars = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
			for (var i = 0; i < 3; i++) {
				var id = Math.ceil(Math.random() * 25);
				res += chars[id];
			}
			$("#sn").val(res);
		},
		pass: function () {
			var chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
			var res = '';
			for (var i = 0; i < 10; i++) {
				var id = Math.ceil(Math.random() * 35);
				res += chars[id];
			}
			$("#passwd").val($.md5(res));
		},
		user: function (type) {
			if (!type || type == 'undefined') {
				type = 'email';
			}
			var uid = $("#user_id").val();
			if (!uid) {
				$.dialog.alert(p_lang('未绑定会员账号'));
				return false;
			}
			$.phpok.json(get_url('order', 'user', 'id=' + uid + "&type=" + type), function (rs) {
				if (rs.status) {
					$("#" + type).val(rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		ext_delete: function (obj) {
			$(obj).parent().parent().parent().remove();
		},
		ext_create: function () {
			html = '<div style="margin:2px 0">';
			html += '<ul class="layout">';
			html += '<li><input type="text" name="extkey[]" class="layui-input" /></li>';
			html += '<li>：</li>';
			html += '<li><input type="text" name="extval[]" class="layui-input default" /></li>';
			html += '<li><input type="button" value=" - " onclick="$.admin_order.ext_delete(this)" class="layui-btn" /></li>';
			html += '</ul></div>';
			$("#ext_html").append(html);
		},
		product_virtual: function (val) {
			if (val == 1) {
				$("#product_not_virtual").hide();
			} else {
				$("#product_not_virtual").show();
			}
		},
		prolist: function () {
			var url = get_url('order', 'prolist');
			var id = $("#tid").val();
			if (id) {
				url += "&id=" + id;
			}
			var currency_id = $("#currency_info").attr('data-id');
			if (currency_id) {
				url += '&currency_id=' + currency_id;
			}

			$.dialog.open(url, {
				'title': p_lang('选择商品'),
				'width': '70%',
				'height': '70%',
				'lock': true,
				'resize': false,
				'fixed': true
			});
		},
		save: function () {
			$("#ordersave").ajaxSubmit({
				'url': get_url('order', 'save'),
				'type': 'post',
				'dataType': 'json',
				'success': function (rs) {
					if (!rs.status) {
						$.dialog.alert(rs.info);
						return false;
					}
					var tip = p_lang('订单创建成功');
					if ($("#id").length > 0) {
						tip = p_lang('订单编辑成功');
					}
					$.dialog.tips(tip, function () {
						$.admin.reload(get_url('order'));
						$.admin.close();
					}).lock();
				}
			});
			return false;
		},
		del: function (id, title) {
			var tip = p_lang('确定要删除订单 {title} 吗？<br />删除后您不能再恢复，请慎用', '<span class="red">' + title + '</span>');
			$.dialog.confirm(tip, function () {
				var url = get_url('order', 'delete', 'id=' + id);
				$.phpok.json(url, function (data) {
					if (data.status) {
						$.dialog.tips(p_lang('订单删除成功'));
						$("#edit_"+id).remove();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		show: function (id) {
			var url = get_url('order', 'info', 'id=' + id);
			$.dialog.open(url, {
				'title': p_lang('查看订单') + "_#" + id,
				'lock': true,
				'width': '70%',
				'height': '70%',
				'cancel': function () {
					return true;
				},
				'cancelVal': p_lang('关闭')
			})
		},
		payment: function (id) {
			var url = get_url('order', 'payment', 'id=' + id);
			$.win(p_lang('订单支付') + "_#" + id, url);
		},
		express: function (id) {
			if (!id || id == 'undefined') {
				var id = $.checkbox.join(".layui-table");
				if (!id) {
					$.dialog.alert(p_lang('请选择要操作的订单'));
					return false;
				}
				console.log(id);
				if (id.indexOf(',') !== -1) {
					$.dialog.alert(p_lang('物流快递每次只能一个订单'));
					return false;
				}
			}
			url = get_url('order', 'express', 'id=' + id);
			$.dialog.open(url, {
				'title': p_lang('物流快递，您的订单编号') + '_#<span class="red">' + id + '</span>',
				'width': '70%',
				'height': '70%',
				'lock': true,
				'cancelVal': p_lang('关闭'),
				'cancel': true
			});
		},
		cancel: function () {
			var id = $.checkbox.join();
			if (!id) {
				$.dialog.alert(p_lang('请选择要操作的订单'));
				return false;
			}
			if (id.indexOf(',') !== -1) {
				$.dialog.alert(p_lang('取消操作每次只能一个订单'));
				return false;
			}
			var sn = $("td[data-id=" + id + "]").attr("data-sn");
			var status = $("td[data-id=" + id + "]").attr('data-status');
			if (status == 'end') {
				$.dialog.alert(p_lang('订单已完成，不能执行取消操作'));
				return false;
			}
			if (status == 'stop') {
				$.dialog.alert(p_lang('订单已结束，不能执行取消操作'));
				return false;
			}
			if (status == 'cancel') {
				$.dialog.alert(p_lang('不能重复执行取消操作'));
				return false;
			}
			var tip = p_lang('确定要取消订单{sn}吗？<br/>请填写理由', ' <span class="red">' + sn + '</span> ');
			$.dialog.prompt(tip, function (val) {
				if (!val) {
					$.dialog.alert(p_lang('取消理由不能为空'));
					return false;
				}
				var url = get_url('order', 'cancel', 'id=' + id + "&note=" + $.str.encode(val));
				$.phpok.json(url, function (data) {
					if (data.status) {
						$.dialog.tips(p_lang('订单取消成功'), function () {
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			}, '');
		},
		stop: function () {
			var id = $.checkbox.join();
			if (!id) {
				$.dialog.alert(p_lang('请选择要操作的订单'));
				return false;
			}
			if (id.indexOf(',') !== -1) {
				$.dialog.alert(p_lang('结束操作每次只能一个订单'));
				return false;
			}
			var sn = $("td[data-id=" + id + "]").attr("data-sn");
			var status = $("td[data-id=" + id + "]").attr('data-status');
			if (status == 'end') {
				$.dialog.alert(p_lang('订单已完成，不能执行结束操作'));
				return false;
			}
			if (status == 'cancel') {
				$.dialog.alert(p_lang('订单已取消，不能执行取消操作'));
				return false;
			}
			if (status == 'stop') {
				$.dialog.alert(p_lang('不能重复执行取消操作'));
				return false;
			}
			var tip = p_lang('确定要结束该订单吗？执行后订单') + '<br/><span class="red">' + sn + '</span>';
			$.dialog.confirm(tip, function () {
				var url = get_url('order', 'stop', 'id=' + id);
				$.phpok.json(url, function (data) {
					if (data.status) {
						$.dialog.tips(p_lang('订单已结束'), function () {
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		set_order: function (id, status) {
			var html = '<select id="ostatus">' + $("#statuslist select").html() + "</select>";
			$.dialog({
				'id': 'openstatus',
				'title': p_lang('订单') + "_#" + id,
				'content': html,
				'init': function () {
					$("#ostatus").find("option[value=" + status + "]").attr("selected", true);
				},
				'lock': true,
				'width': '300px',
				'height': '100px',
				'ok': function () {
					var st = $("#ostatus").val();
					var url = get_url('order', 'status', 'id=' + id + "&status=" + st);
					$.phpok.json(url, function (rs) {
						if (!rs.status) {
							$.dialog.alert(rs.info);
							return false;
						}
						$.dialog.tips(p_lang('订单已操作成功'), function () {
							$.phpok.reload();
						}).lock();
						return true;
					});
				},
				'okVal': p_lang('修改'),
				'cancel': true,
				'cancelVal': p_lang('取消')
			});
		},
		finish: function () {
			var id = $.checkbox.join();
			if (!id) {
				$.dialog.alert(p_lang('请选择要操作的订单'));
				return false;
			}
			if (id.indexOf(',') !== -1) {
				$.dialog.alert(p_lang('完成操作每次只能一个订单'));
				return false;
			}
			var sn = $("td[data-id=" + id + "]").attr("data-sn");
			var status = $("td[data-id=" + id + "]").attr('data-status');
			if (status == 'end') {
				$.dialog.alert(p_lang('不能重复执行取消操作'));
				return false;
			}
			if (status == 'cancel') {
				$.dialog.alert(p_lang('订单已取消，不能执行完成操作'));
				return false;
			}
			if (status == 'stop') {
				$.dialog.alert(p_lang('订单已结束，不能执行完成操作'));
				return false;
			}
			var tip = p_lang('确定该订单已完成吗？') + '<br/><span class="red">' + sn + '</span>';
			$.dialog.confirm(tip, function () {
				var url = get_url('order', 'end', 'id=' + id);
				$.phpok.json(url, function (data) {
					if (data.status) {
						$.dialog.tips(p_lang('订单已完成'), function () {
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	}
})(jQuery);

$(document).ready(function () {
	layui.use('laydate', function () {
		var laydate = layui.laydate;
		//执行一个laydate实例
		laydate.render({
			elem: '#date_start' //指定元素
		});
		laydate.render({
			elem: '#date_stop' //指定元素
		});
	});
});