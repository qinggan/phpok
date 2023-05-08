/**
 * 购物车页公共脚本
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月14日
**/

function check_it()
{
	var act = $.dialog.tips('正在创建订单，请稍候…',100).lock();
	var url = api_url('order','create');
	$.phpok.submit($("#saveorder")[0],url,function(rs){
		if(!rs.status){
			act.content(rs.info).time(1.5);
			return false;
		}
		act.content('订单创建成功，订单号是：'+rs.info.sn);
		var ext = parseInt(user_id) > 0 ? 'id='+rs.info.id : 'sn='+rs.info.sn+"&passwd="+rs.info.passwd;
		var extOrder = ext;
		var payment = $("input[name=payment]:checked").val();
		if(payment && payment !=0){
			ext += '&payment='+payment.toString();
			$("input[data-name=integral]").each(function(i){
				var name = $(this).attr('data-key');
				var val = $(this).val();
				if(parseInt(val) > 0){
					ext += "&integral_val["+name+"]="+val;
				}
			});
			url = api_url('payment','create',ext);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info).lock();
					$.phpok.go(get_url('order','info',extOrder));
					return false;
				}
				if(!rs.info){
					$.phpok.go(get_url('order','info',extOrder));
					return false;
				}
				url = get_url('payment','action','id='+rs.info);
				$.phpok.go(url);
			});
			return true;
		}
		var url = get_url('order','info',ext);
		$.phpok.go(get_url('usercp','','link='+$.str.encode(url)+"&title="+$.str.encode('订单详情_#'+rs.info.id)));
		return true;
	});
	return false;
}

function load_freight()
{
	var id = new Array();
	$("input[data-name=product]").each(function(i){
		id.push($(this).val());
	});
	var url = api_url('cart','pricelist','ids='+id.join(","));
	if($("input[name=address_id]").length > 0){
		var address_id = $("input[name=address_id]:checked").val();
		if(address_id){
			url += "&address_id="+address_id;
		}
	}else{
		var province = $("#pca_p").val();
		var city = $("#pca_c").val();
		if(province && city){
			url += "&province="+$.str.encode(province)+"&city="+$.str.encode(city);
		}
	}
	$.phpok.json(url,function(rs){
		if(rs.status){
			var list = rs.info;
			var html = '<table class="table ">';
			var total = 0;
			for(var i in list){
				if(!list[i].price || list[i].price == 'undefined'){
					continue;
				}
				html += '<tr>';
				html += '<td class="text-right">';
				html += '<input type="hidden" name="ext_price['+list[i].identifier+']" id="ext_price_'+list[i].identifier+'" value="'+list[i].price_val+'" />';
				html += list[i].title+'：</td>';
				html += '<td id="'+list[i].identifier+'_price" class="text-danger">'+list[i].price+'</td>';
				html += '</tr>';
				total += parseFloat(list[i].price_val);
			}
			html += '<tr>';
			html += '<td class="text-right">总价：</td>';
			html += '<td id="all_price" class="text-danger" style="width:150px;">'+total.toFixed(2)+'元</td>';
			html += '</tr>';
			html += '</table>';
			$("#price_info").html(html);
			$("#price_info_panel").show();
			return true;
		}
		$("#price_info_panel").hide();
		$.dialog.tips(rs.info);
		return false;
	})
}

function update_coupon()
{
	var code = $("#coupon").val();
	if(!code){
		$.dialog.alert('优惠码不能为空');
		return false;
	}
	var url = api_url('coupon','use','code='+$.str.encode(code));
	var tip = $.dialog.tips('正在检测优惠码，请稍候…',100).lock();
	$.phpok.json(url,function(rs){
		tip.close();
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		$.dialog.tips('请稍候，正在刷新页面…',function(){
			$.phpok.reload();
		}).lock().time(2);
		return true;
	})
}
