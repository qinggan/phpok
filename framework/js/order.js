/***********************************************************
	Filename: {phpok}/js/order.js
	Note	: 订单JS相关动作管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月18日
***********************************************************/
//添加一行
function add_row()
{
	var total = $("#prolist tr.prolist").length;
	if($("#pro_"+total).length>0) total = total.toString() + "_"+ (parseInt(100*Math.random())).toString();
	var html = '<tr id="pro_'+total+'" class="prolist">';
	html += '<input type="hidden" name="pro_id[]" value="add" />';
	html += '<input type="hidden" name="pro_tmp[]" value="'+total+'" />';
	html += '<input type="hidden" name="pro_tid[]" id="pro_tid_'+total+'" value="0" class="p_proid" />';
	html += '<input type="hidden" name="pro_thumb[]" id="pro_thumb_'+total+'" value="0" />';
	html += '<td align="center" id="pro_thumb_view_'+total+'"><img src="images/picture_default.png" width="80px" height="80px" border="0" onclick="update_pic(\''+total+'\')" style="cursor:pointer;" /></td>';
	html += '<td>';
	html += '<table><tr><td>名称：<input type="text" name="pro_title[]" class="long" id="pro_title_'+total+'" /></td></tr>';
	html += '<tr><td>价格：<input type="text" id="pro_price_'+total+'" name="pro_price[]" class="price" />';
	html += ' 重量：<input type="text" name="pro_weight[]" id="pro_weight_'+total+'" class="short" value="0" /> Kg';
	html += ' 体积：<input type="text" name="pro_volume[]" class="short" id="pro_volume_'+total+'" value="0" /> M<sup>3</sup>';
	html += '<input type="button" value="选择产品" onclick="pro_select(\''+total+'\')" class="btn" /></td></tr>';
	html += '<tr><td>备注：<input type="text" name="pro_note[]" id="pro_note_'+total+'" class="default" />';
	html += '<select name="pro_virtual[]" id="pro_virtual_'+total+'"><option value="0">实物</option><option value="1">虚拟/服务</option>';
	html += '</select>';
	html += '</td></tr></table>';
	html += '</td>';
	html += '<td style="padding:0;background:#fefefe;" valign="top">';
	html += '<table cellpadding="0" cellspacing="0">';
	html += '<tr><th width="45%" class="lft">名称</th><th width="45%" class="lft">内容</th>';
	html += '<th class="hand" onclick="order_attr_add(\''+total+'\',this)">+</th></tr>';
	html +='</table></td>';
	html += '<td class="center"><div><input type="text" name="pro_qty[]" class="qty" value="1" /></div>';
	html += '<div style="margin-top:10px;">';
	html += '<input type="text" name="pro_unit[]" class="qty" id="pro_unit_'+total+'" placeholder="单位" /></div>'
	html += '</td>';
	html += '<td class="center"><input type="button" value="删除" onclick="order_pro_delete2(\''+total+'\')" class="btn" /></td>';
	html += '</tr>';
	$("#prolist").append(html);
}

function order_attr_add(num,obj)
{
	var html = '<tr>';
	html += '<td><input type="text" name="ext_title_'+num+'[]" style="width:100%" /></td>';
	html += '<td><input type="text" name="ext_content_'+num+'[]" style="width:100%" /></td>';
	html += '<td><input type="button" value="-" onclick="order_attr_remove(this)" /></td>';
	html += '</tr>';
	$(obj).parent().parent().append(html);
}

function order_attr_remove(obj)
{
	$(obj).parent().parent().remove();
}

//弹出窗口选取商品
function pro_select(id)
{
	var url = get_url('order','prolist','id='+$.str.encode(id));
	var currency_id = $("#currency_id").val();
	url += '&currency_id='+currency_id;
	var ids='';
	$("input.p_proid").each(function(i){
		var t = $(this).val();
		if(t && t != '0' && t != 'undefined'){
			ids += t+",";
		}
	});
	if(ids){
		ids = ids.substr(0,(ids.length - 1));
		url += "&exinclude="+$.str.encode(ids);
	}
	$.dialog.open(url,{
		'title':'选择商品',
		'width':'70%',
		'height':'70%',
		'lock':true,
		'resize':false,
		'fixed':true
	});
}

//编辑订单
function order_edit(id)
{
	var url = get_url('order','set','id='+id);
	direct(url);
}

function order_pro_delete2(id)
{
	$("#pro_"+id).remove();
}

function order_delete(id,title)
{
	$.dialog.confirm('确定要删除订单：<span class="red">'+title+'</span> 吗?<br />删除后您不能再恢复，请慎用<br /><span class="darkblue">删除成功后系统会自动刷新当前页面且不提示</span>',function(){
		var url = get_url('order','delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.phpok.reload();
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function order_check(id,title)
{
	$.dialog.confirm('确定要审核订单：<span class="red">'+title+'</span> 吗?<br />订单审核成功后，前台将没有删除权限',function(){
		var url = get_url('order','status','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.phpok.reload();
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

//更新密码
function update_passwd()
{
	var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	var res = '';
	for(var i = 0; i < 10 ; i ++)
	{
		var id = Math.ceil(Math.random()*35);
		res += chars[id];
    }
    $("#passwd").val($.md5(res));
}

function update_sn()
{
	var res = 'KF';
	var myDate = new Date();
	res += myDate.getFullYear();
	var month = myDate.getMonth() + 1;
	if(month.length == 1)
	{
		month = '0'+month.toString();
	}
	res += month;
	var date = myDate.getDate();
	if(date.length == 1)
	{
		date = '0'+date.toString();
	}
	res += date;
	var hour = myDate.getHours() + 1;
	if(hour.length == 1)
	{
		hour = '0'+hour.toString();
	}
	res += hour;
	var minutes = myDate.getMinutes();
	if(minutes.length == 1)
	{
		minutes = '0'+minutes.toString();
	}
	res += minutes;
	var seconds = myDate.getSeconds();
	if(seconds.length == 1)
	{
		seconds = '0'+seconds.toString();
	}
	res += seconds;
	var chars = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	for(var i = 0; i < 3 ; i ++)
	{
		var id = Math.ceil(Math.random()*25);
		res += chars[id];
    }
    $("#sn").val(res);
}

//删除订单产品，删除操作成功会更新订单金额
function order_pro_delete(id,numid)
{
	var title = $("#pro_title_"+numid).val();
	$.dialog.confirm("确定要删除产品：<span class='darkblue'>"+title+"</span><br /><strong class='red'>删除后会刷新当前面，并重新计算产品价格，请慎重选择</strong>",function(){
		var url = get_url('order','product_delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.dialog.alert("产品：<span class='darkblue'>"+title+"</span> 已成功删除！",function(){
				$.phpok.reload();
			});
		}
		else
		{
			if(!rs.content) rs.content = '删除失败';
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

//通过Ajax加载产品信息
function load_product(num,id)
{
	var url = get_url('order','product','id='+id);
	var currency_id = $("#currency_id").val();
	url += '&currency_id='+currency_id;
	var rs = $.phpok.json(url);
	if(rs.status != 'ok'){
		if(!rs.content){
			rs.content = '产品信息获取失败';
		}
		$.dialog.alert(rs.content);
		return false;
	}
	$("#pro_tid_"+num).val(id);
	if(rs.content.thumb){
		$("#pro_thumb_"+num).val(rs.content.thumb.filename);
		$("#pro_thumb_view_"+num).html('<img src="'+rs.content.thumb.ico+'" border="0" width="80px" height="80px" onclick="update_pic(\''+num+'\')" style="cursor:pointer;" />');
	}
	$("#pro_title_"+num).val(rs.content.title);
	$("#pro_price_"+num).val(rs.content.price);
	$("#pro_weight_"+num).val(rs.content.weight);
	$("#pro_volume_"+num).val(rs.content.volume);
	$("#pro_unit_"+num).val(rs.content.unit);
	if(rs.content.is_virtual == 1){
		$("#pro_virtual_"+num+" option[value=1]").attr('selected','selected');
	}else{
		$("#pro_virtual_"+num+" option[value=0]").attr('selected','selected');
	}
	update_price();
	return true;
}

//更新附件
function update_pic(tid)
{
	var url = get_url('order','thumb','id='+tid);
	$.dialog.open(url,{
		'title':'图片库',
		'width':'50%',
		'height':'80%',
		'lock':true,
		'resize':false,
		'fixed':true
	});
}

function such_as_shipping()
{
	$("#b-fullname").val($("#s-fullname").val());
	$("#b-country").val($("#s-country").val());
	$("#b-province").val($("#s-province").val());
	$("#b-city").val($("#s-city").val());
	$("#b-county").val($("#s-county").val());
	$("#b-address").val($("#s-address").val());
	$("#b-zipcode").val($("#s-zipcode").val());
	$("#b-tel").val($("#s-tel").val());
	$("#b-mobile").val($("#s-mobile").val());
	$("#b-email").val($("#s-email").val());
	var gender = $("input[name=s-gender]:checked").val();
	$("input[name=b-gender][value="+gender+"]").attr("checked",true);
}

function get_user_email()
{
	var uid = $("#user_id").val();
	if(!uid){
		$.dialog.alert(p_lang('未绑定会员账号'));
		return false;
	}
	var url = get_url('user','info','uid='+uid);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			if(!rs.content.email){
				$.dialog.alert(p_lang('会员没有绑定邮箱'));
				return false;
			}
			$("#email").val(rs.content.email);
			return true;
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	})
}

function get_user_mobile()
{
	var uid = $("#user_id").val();
	if(!uid){
		$.dialog.alert(p_lang('未绑定会员账号'));
		return false;
	}
	var url = get_url('user','info','uid='+uid);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			if(!rs.content.mobile){
				$.dialog.alert(p_lang('会员没有绑定手机号'));
				return false;
			}
			$("#mobile").val(rs.content.mobile);
			return true;
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	})
}


function total_price()
{
	var total = 0;
	$("input[sign=ext_price]").each(function(i){
		var val = $(this).val();
		val = parseFloat(val);
		if(isNaN(val)){
			val = 0;
		}
		if($(this).attr("action") == 'add'){
			total += val;
		}else{
			total -= val;
		}
	});
	$('#price').val(total.toString());
}

function order_express(id,sn)
{
	var url = get_url('order','express_check','id='+id);
	var rs = $.phpok.json(url);
	if(rs.status){
		if(rs.info > 0){
			url = get_url('order','express','id='+id);
			$.dialog.open(url,{
				'title':p_lang('物流快递，您的订单编号是：')+'<span class="red">'+sn+'</span>',
				'width':'70%',
				'height':'70%',
				'lock':true,
				'cancelVal':p_lang('关闭'),
				'cancel':function(){return true;}
			});
		}else{
			$.dialog.alert(p_lang('订单中没有实物，不需要填写物流信息'));
			return false;
		}
	}
}

function update_price()
{
	var val = 0;
	$("tr[class=prolist]").each(function(){
		var price = $(this).find("input[class=price]").val();
		var qty = $(this).find('input[class=qty]').val();
		if(!qty){
			qty = 1;
		}
		price = parseFloat(price);
		qty = parseInt(qty);
		var t = price * qty;
		if(t>0){
			val = val + t;
		}
	});
	val = val.toFixed(2);
	$("#ext_price_product").val(val.toString());
	total_price();
}

function save_order()
{
	$("#ordersave").ajaxSubmit({
		'url':get_url('order','save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			//订单状态为否时
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			var id = $("#id").val();
			var sn = $("#sn").val();
			var tip = p_lang('订单编辑成功');
			if(id && id == '0'){
				tip = p_lang('订单创建成功');
			}
			$.dialog.alert(tip,function(){
				$.phpok.go(get_url('order'));
			},'succeed');
		}
	});
}

function update_keywords(val){
	if(val == 'time'){
		$("#keywords").bind("focus",function(){
			laydate();
		}).val('');
	}else{
		$("#keywords").unbind('focus').val('');
	}
}

function order_info_show(id,sn)
{
	var url = get_url('order','info','id='+id);
	$.dialog.open(url,{
		'title':p_lang('查看订单：')+sn,
		'lock':true,
		'width':'70%',
		'height':'70%',
		'cancel':function(){
			return true;
		},
		'cancelVal':p_lang('关闭')
	})
}

/**
 * 结束订单，无论订单进行到哪一步，后台管理员都能在这里直接中止订单
 * @参数 id 订单ID号
 * @参数 sn 订单SN号
**/
function order_stop(id,sn)
{
	var url = get_url('order','end','act=stop&id='+id);
	var price = $("td[data-id="+id+"]").attr("data-unpaid");
	var tip = '';
	if(parseFloat(price)>0){
		var text = $("td[data-unpaid-text="+id+"]").text();
		tip = '<div>'+p_lang('未支付金额：')+'<span class="red">'+text+'</span></div>';
	}
	$.dialog.confirm(p_lang('确定要结束这个订单吗？')+tip+'<div>'+p_lang('订单号：')+'<span class="darkblue">'+sn+'</span></div>',function(){
		var rs = $.phpok.json(url);
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	});
}

function order_cancel(id,sn)
{
	var url = get_url('order','end','act=cancel&id='+id);
	var price = $("td[data-id="+id+"]").attr("data-unpaid");
	var tip = '';
	if(parseFloat(price)>0){
		var text = $("td[data-unpaid-text="+id+"]").text();
		tip = '<div>'+p_lang('未支付金额：')+'<span class="red">'+text+'</span></div>';
	}
	$.dialog.confirm(p_lang('确定要取消这个订单吗？')+tip+'<div>'+p_lang('订单号：')+'<span class="darkblue">'+sn+'</span></div>',function(){
		var rs = $.phpok.json(url);
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	});
}

function order_end(id,sn)
{
	var url = get_url('order','end','act=end&id='+id);
	var price = $("td[data-id="+id+"]").attr("data-unpaid");
	var tip = '';
	if(parseFloat(price)>0){
		var text = $("td[data-unpaid-text="+id+"]").text();
		tip = '<div>'+p_lang('未支付金额：')+'<span class="red">'+text+'</span></div>';
	}
	$.dialog.confirm(p_lang('这个订单已经完成全部流程了吗？')+tip+'<div>'+p_lang('订单号：')+'<span class="darkblue">'+sn+'</span></div>',function(){
		var rs = $.phpok.json(url);
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	});
}

function order_payment(id,sn)
{
	var url = get_url('order','payment','id='+id);
	$.dialog.open(url,{
		'title':p_lang('订单支付：')+sn,
		'lock':true,
		'width':'70%',
		'height':'70%',
		'ok':function(){
			$.phpok.reload();
		},
		'okVal':p_lang('关闭并刷新'),
		'cancel':function(){
			return true;
		},
		'cancelVal':p_lang('关闭')
	})
}