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
	$("#src_code").attr("src",$.phpok.nocache(src_url));
}

//验证并登录
function admlogin()
{
	$("#adminlogin").ajaxSubmit({
		'url':get_url('login','ok'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.phpok.go(get_url('index'));
				return true;
			}
			$.dialog.alert(rs.info,function(){
				$("#code_id").val('');
				login_code('admin');
			},'error');
			return false;
		}
	});
	return false;
}

function update_lang(val)
{
	var url = get_url('login','','_langid='+val);
	$.phpok.go(url);
}

$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
});