/***********************************************************
	Filename: {phpok}/js/user.js
	Note	: 用户管理中涉及到的JS
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
		$.dialog.alert("用户账号不能为空");
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
	if(!id){
		$.dialog.alert("操作非法");
		return false;
	}
	$.dialog.confirm(p_lang('确定要删除用户号ID为 {id} 的用户信息吗？<br>删除后数据将被清空且不能恢复','<span class="red">#'+id+'</span>'),function(){
		var url = get_url('user','ajax_del','id='+id);
		$.phpok.ajax(url,function(data){
			if(data == 'ok'){
				$.phpok.reload();
			}else{
				if(!data){
					data = p_lang('删除用户操作异常');
					$.dialog.alert(data);
				}
			}
		});
		return true;
	});
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

