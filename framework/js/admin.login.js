/***********************************************************
	Filename: {phpok}/js/admlogin.js
	Note	: 管理员登录页涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月6日
***********************************************************/
//验证码
function login_code(appid)
{
	var src_url = api_url("vcode","","id="+appid);
	$("#src_code").attr("src",src_url);
}

//验证并登录
function admlogin()
{
	var username = $("#username").val();
	if(!username)
	{
		$.dialog.alert("管理员账号不能为空",false,'error');
		return false;
	}
	//密码验证
	var pass = $("#password").val();
	if(!pass)
	{
		$.dialog.alert("密码不能为空！",false,'error');
		return false;
	}
	var url = get_url('login','check','user='+$.str.encode(username)+"&pass="+$.str.encode(pass));
	var vcode = $("#code_id").val();
	if(vcode)
	{
		url += "&_code="+$.str.encode(vcode);
	}
	var rs = $.phpok.json(url);
	if(rs.status != 'ok')
	{
		$.dialog.alert(rs.content,function(){
			$("#code_id").val('');
			login_code('admin');
		},'error');
		return false;
	}
	else
	{
		url = get_url('index');
		$.phpok.go(url);
	}
	return false;
}

