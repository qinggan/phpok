/**************************************************************************************************
	文件： {phpok}/js/payment.js
	说明： 支付管理中涉及到的JS
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年4月25日
***************************************************************************************************/
function group_edit(id)
{
	var url = get_url('payment','groupset','id='+id);
	$.phpok.go(url);
}

function group_delete(id,title)
{
	var q = confirm('确定要删除支付组【'+title+'】 吗？删除后是不能恢复的！');
	if(q == '0')
	{
		return false;
	}
	var url = get_url('payment','groupdel','id='+id);
	var rs = $.phpok.json(url);
	if(!rs || rs.status != 'ok')
	{
		alert(rs.content);
		return false;
	}
	$.phpok.reload();
}

function payment_add(gid)
{
	var url = get_url('payment','set','gid='+gid);
	$.dialog({
		'title':"请选择要支付的类型",
		'content':document.getElementById("payment_select_info"),
		'ok':function(){
			var code = $("#code").val();
			if(!code)
			{
				alert('请选择要创建的支付引挈');
				return false;
			}
			url += "&code="+code;
			$.phpok.go(url);
			return true;
		},
		'cancel':function(){}
	});
}

//提交支付内容时的验证
function check_payment_set()
{
	if(!$("#title").val())
	{
		alert('支付名称不能为空');
		return false;
	}
	if(!$("#code").val())
	{
		alert('支付引挈异常，请重新操作');
		$.phpok.go(url);
		return false;
	}
	return true;
}

//编辑支付方案
function payment_edit(id)
{
	url = get_url('payment','set','id='+id)
	$.phpok.go(url);
}

//删除支付方案
function payment_delete(id,title)
{
	$.dialog.confirm("确定要删除支付方案：<span class='red'>"+title+"</span> 吗？删除后是不能恢复的",function(){
		var url = get_url('payment','delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.dialog.alert('支付方案删除成功',function(){
				$.phpok.reload();
			},'succeed');
			return false;
		}
		else
		{
			$.dialog.alert(rs.content,'','error');
			return false;
		}
	});
}