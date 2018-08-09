/**
 * 管理员登录页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月07日
**/

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