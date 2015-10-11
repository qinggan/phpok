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
	html += '<input type="hidden" name="pro_tid[]" id="pro_tid_'+total+'" value="0" class="p_proid" />';
	html += '<input type="hidden" name="pro_thumb[]" id="pro_thumb_'+total+'" value="0" />';
	html += '<td align="center" id="pro_thumb_view_'+total+'"><img src="images/picture_default.png" width="80px" height="80px" border="0" onclick="update_pic(\''+total+'\')" style="cursor:pointer;" /></td>';
	html += '<td>';
	html += '<table><tr><td><input type="text" name="pro_title[]" class="long" id="pro_title_'+total+'" placeholder="产品名称" /></td></tr><tr><td><input type="text" id="pro_price_'+total+'" name="pro_price[]" class="price" placeholder="产品单价" /> <input type="button" value="选择产品" onclick="pro_select(\''+total+'\')" class="btn" /></td></tr></table>';
	html += '</td>';
	html += '<td class="center"><input type="text" name="pro_qty[]" class="qty" value="1" /></td>';
	html += '<td class="center"><input type="button" value="删除" onclick="order_pro_delete2(\''+total+'\')" class="btn" /></td>';
	html += '</tr>';
	$("#prolist").append(html);
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
			$("#email").val(rs.content.email);
			return true;
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	})
}

function get_user_invoice()
{
	var uid = $("#user_id").val();
	if(!uid){
		$.dialog.alert(p_lang('未绑定会员账号'));
		return false;
	}
	var url = get_url('user','info','uid='+uid+"&type=invoice");
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			var info = rs.content.rs;
			var list = rs.content.rslist;
			invoice_show_select(list,info.id);
			$("#invoice_type").val(info.type);
			$("#invoice_title").val(info.title);
			$("#invoice_content").val(info.content);
			return true;
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	})
}
function update_user_invoice(obj)
{
	var obj = $(obj).find("option:selected");
	$("#invoice_type").val(obj.attr('type'));
	$("#invoice_title").val(obj.attr('title'));
	$("#invoice_content").val(obj.attr('content'));
}

function invoice_show_select(list,id)
{
	var html = '<select onchange="update_user_invoice(this)">';
	for(var i in list){
		html += '<option value="'+list[i].id+'" type="'+list[i].type+'" title="'+list[i].title+'" content="'+list[i].content+'"';
		if(list[i].id == id){
			html += ' selected';
		}
		html += '>'+list[i].type+'/'+list[i].title+'</option>';
	}
	html += '</select>';
	$("#invoice_user_select").html(html);
}
function update_user_address(obj)
{
	var obj = $(obj).find("option:selected");
	$("#s-fullname").val(obj.attr('fullname'));
	$("#s-country").val(obj.attr('country'));
	$("#s-province").val(obj.attr('province'));
	$("#s-city").val(obj.attr('city'));
	$("#s-county").val(obj.attr('county'));
	$("#s-address").val(obj.attr('address'));
	$("#s-mobile").val(obj.attr('mobile'));
	$("#s-tel").val(obj.attr('tel'));
	$("#s-email").val(obj.attr('email'));
}
function user_show_select(list,id)
{
	var html = '<select onchange="update_user_address(this)">';
	for(var i in list){
		html += '<option value="'+list[i].id+'" fullname="'+list[i].fullname+'" country="'+list[i].country+'" ';
		html += 'city="'+list[i].city+'" province="'+list[i].province+'" county="'+list[i].county+'"';
		html += 'address="'+list[i].address+'" mobile="'+list[i].mobile+'" tel="'+list[i].tel+'" email="'+list[i].email+'"';
		if(list[i].id == id){
			html += ' selected';
		}
		html += '>'+list[i].fullname+'：'+list[i].province+list[i].city+list[i].county+list[i].address;
		if(list[i].mobile){
			html += '/'+list[i].mobile;
		}
		html += '</option>'
	}
	html += '</select>';
	$("#address_user_select").html(html);
}

function get_user_address()
{
	var uid = $("#user_id").val();
	if(!uid){
		$.dialog.alert(p_lang('未绑定会员账号'));
		return false;
	}
	var url = get_url('user','info','uid='+uid+"&type=address");
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			var info = rs.content.rs;
			var list = rs.content.rslist;
			user_show_select(list,info.id);
			$("#s-fullname").val(info.fullname);
			$("#s-country").val(info.country);
			$("#s-province").val(info.province);
			$("#s-city").val(info.city);
			$("#s-county").val(info.county);
			$("#s-address").val(info.address);
			$("#s-mobile").val(info.mobile);
			$("#s-tel").val(info.tel);
			$("#s-email").val(info.email);
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
	$('#price,#pay_price').val(total.toString());
}

function order_express(id,sn)
{
	var url = get_url('order','express','id='+id);
	$.dialog.open(url,{
		'title':p_lang('物流快递，您的订单编号是：')+'<span class="red">'+sn+'</span>',
		'width':'70%',
		'height':'70%',
		'lock':true
	});
}