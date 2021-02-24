/**
 * 管理员登录页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月07日
**/
var time_obj;
var wait_obj;

//验证并登录
function admin_login()
{
	$("#post_save").ajaxSubmit({
		'url':get_url('login','update'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			if(rs.info){
				$.phpok.data('admin',rs.info);
			}
			$.dialog.tips('管理员登录成功',function(){
				$.phpok.go(get_url('login','mobile_success'));
			}).lock();
			return false;
		}
	});
	return false;
}

function q_adm_login(time)
{
	var url = get_url('login','update','login_time='+time);
	var quickcode = $("#quickcode").val();
	if(!quickcode){
		$.dialog.alert('管理员信息不存在，请用账号密码登录');
		return false;
	}
	var fid = $("#fid").val();
	var fcode = $("#fcode").val();
	if(!fid || !fcode){
		$.dialog.alert('登录数据不完整');
		return false;
	}
	url += "&quickcode="+$.str.encode(quickcode)+"&fid="+fid+"&fcode="+fcode;
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		if(rs.info){
			$.phpok.data('admin',rs.info);
		}
		$.dialog.tips('管理员登录成功',function(){
			$.phpok.go(get_url('login','mobile_success'));
		}).lock();
		return true;
	})
}


$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
	//获取本机存储的信息
	var info = $.phpok.data("admin");
	var fid = $("#fid").val();
	if(info && fid){
		$("#quicklogin").hide();
		var url = get_url('login','checkadm','content='+$.str.encode(info)+"&fid="+fid);
		$.phpok.json(url,function(rs){
			if(!rs.status){
				$.phpok.undata('admin');
				return false;
			}
			//验证通过生成快登录界面
			var txt = '管理员 <span style="color:red">' + rs.info.account + '</span> 快捷登录';
			$("h3").html(txt);
			$("#quickcode").val(rs.info.logincode);
			$("#quicklogin").show();
		});
	}
});