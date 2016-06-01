/***********************************************************
	Filename: {phpok}/js/user.js
	Note	: 会员管理中涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月2日
***********************************************************/
//检查添加操作
function check_add()
{
	var url = get_url("user","chk");
	var id = $("#id").val();
	if(id && id != "undefined"){
		url += "&id="+id;
	}
	var user = $("#user").val();
	if(!user || user == "undefined"){
		$.dialog.alert("会员账号不能为空");
		return false;
	}
	url += "&user="+$.str.encode(user);
	var mobile = $("#mobile").val();
	if(mobile){
		url += "&mobile="+$.str.encode(mobile);
	}
	var email = $("#email").val();
	if(email){
		url += "&email="+$.str.encode(email);
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

function del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此信息吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = get_url("user","ajax_del") + "&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			window.location.reload();
		}
		else
		{
			alert(msg);
			return false;
		}
	}
}

//更改权限状态
function set_status(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var t = $("#status_"+id).attr("value");
	if(t == 2)
	{
		$.dialog.alert("此会员已被锁定，请点编辑后进行解除锁定");
		return false;
	}
	var url = get_url("user","ajax_status") + "&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id).removeClass("status"+t).addClass("status"+n_t);
		$("#status_"+id).attr("value",n_t);
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}
function action_wealth_select(val)
{
	if(val == '1'){
		$("#a_html").html('增加');
		$("#a_type").val("+");
	}else{
		$("#a_html").html('减少');
		$("#a_type").val("-");
	}
}
function action_wealth(title,wid,uid,unit)
{
	var t_title = p_lang('会员')+title+p_lang('操作');
	var content = '<label><input type="radio" value="+" name="tmp1" checked onclick="action_wealth_select(1)">增加</label> &nbsp; &nbsp; &nbsp;';
	content += '<label><input type="radio" value="-" name="tmp1" onclick="action_wealth_select(2)">减少</label><br /><br />'
	content += '<input type="hidden" id="a_type" value="+" />'
	content += '<span id="a_html">增加</span>：<input type="text" style="width:70px" id="a_val" /> '+unit+'<br /><br />';
	content += p_lang('说明：')+'<input type="text" id="a_note" value="" style="width:300px" />';
	$.dialog({
		'title':t_title,
		'lock':true,
		'content':content,
		'ok':function(){
			var url = get_url('wealth','val','wid='+wid+'&uid='+uid);
			var note = $("#a_note").val();
			if(!note){
				$.dialog.alert(p_lang('请填写相关说明'));
				return false;
			}
			url += "&note="+$.str.encode(note);
			var val = $("#a_val").val();
			if(!val || (val && parseFloat(val)<=0)){
				$.dialog.alert(p_lang('请填写数值，数值必须大于0'));
				return false;
			}
			url += "&val="+val;
			var type = $("#a_type").val();
			if(type){
				url += "&type="+type;
			}
			var rs = $.phpok.json(url);
			if(rs.status == 'ok'){
				$.dialog.alert(p_lang('操作成功'),function(){
					$.phpok.reload();
					return true;
				},'succeed');
			}else{
				$.dialog.alert(rs.content);
				return false;
			}
		},
		'okVal':p_lang('提交'),
		'cancel':function(){
			return true;
		}
	});
}


function show_wealth_log(title,wid,uid)
{
	var url = get_url('wealth','log','wid='+wid+"&uid="+uid);
	$.dialog.open(url,{
		'title':title+p_lang('日志'),
		'lock':true,
		'width':'500px',
		'height':'400px',
		'ok':function(){
			return true;
		},
		'okVal':'关闭'
	});
}

function show_address(title,uid)
{
	var url = get_url('user','address','id='+uid);
	$.dialog.open(url,{
		'title':title+p_lang('地址库')+p_lang('，每个会员最多30个地址'),
		'width':'700px',
		'height':'400px',
		'lock':true,
		'cancel':function(){
			return true;
		},
		'cancelVal':'关闭窗口'
	});
}

function show_invoice(title,uid)
{
	var url = get_url('user','invoice','id='+uid);
	$.dialog.open(url,{
		'title':title+p_lang('发票设置')+p_lang('，每个会员最多10条发票设置'),
		'width':'500px',
		'height':'400px',
		'lock':true,
		'cancel':function(){
			return true;
		},
		'cancelVal':'关闭窗口'
	});
}