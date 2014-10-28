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
		if(t && t != '0' && t != 'undefined')
		{
			ids += t+",";
		}
	});
	if(ids)
	{
		ids = ids.substr(0,(ids.length - 1));
		url += "&exinclude="+$.str.encode(ids);
	}
	$.dialog.open(url,{
		'title':'选择商品',
		'width':'50%',
		'height':'80%',
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

function order_info(id,title)
{
	var url = get_url('order','info','id='+id);
	$.dialog.open(url,{
		'title':'订单：'+title,
		'lock':true,
		'width':'600px',
		'height':'80%',
		'resize':false,
		'fixed':true
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
function order_pro_delete(id)
{
	var title = $("#pro_title_"+id).val();
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
	var rs = json_ajax(url);
	if(rs.status != 'ok')
	{
		if(!rs.content) rs.content = '产品信息获取失败';
		$.dialog.alert(rs.content);
		return false;
	}
	$("#pro_tid_"+num).val(id);
	if(rs.content.thumb)
	{
		$("#pro_thumb_"+num).val(rs.content.thumb.id);
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

//取得地址库
function get_shipping()
{
	order_get_address('shipping');
}

function get_billing()
{
	order_get_address('billing');
}

function order_get_address(type)
{
	var user_id = $("#user_id").val();
	if(!user_id)
	{
		$.dialog.alert("未指定会员，不能执行此操作");
		return false;
	}
	var url = api_url('user','address','type='+type+"&uid="+user_id);
	var rs = json_ajax(url);
	if(rs.status != 'ok')
	{
		if(!rs.content) rs.content = '获取失败';
		$.dialog.alert(rs.content);
		return false;
	}
	var user = rs.content;
	var ext = type == 'shipping' ? 's-' : 'b-';
	$("#"+ext+"fullname").val(user.fullname);
	$("#"+ext+"gender[value='"+user.gender+"']").attr("checked",true);
	$("#"+ext+"address").val(user.address);
	$("#"+ext+"zipcode").val(user.zipcode);
	$("#"+ext+"tel").val(user.tel);
	$("#"+ext+"mobile").val(user.mobile);
	$("#"+ext+"email").val(user.email);
	//更新地址库
	new PCAS(ext+"province",ext+"city",ext+"county",user.province,user.city,user.county);
}