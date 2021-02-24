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


function cLanguage() {
	var l = $("#c-language");
	var s = l.find(".c-select");
	var o = l.find(".c-option");
	var aA = s.find("a");
	s.click(function(event){
		event.stopPropagation()
		if (l.hasClass("open")) {
			l.removeClass("open");
			o.slideUp();
		}else{
			l.addClass("open");
			o.slideDown();
		}
	});

	$(window).click(function(){
		l.removeClass("open");
		o.slideUp();
	})
}

function qrcode_login_checking()
{
	var fid = $("#qrcode-fid").val();
	var content = $("#qrcode-content").val();
	if(!fid || !content){
		$.dialog.alert('数据丢失，请刷新重新获取新二维码');
		return false;
	}
	var url = get_url('login','checking','fid='+fid+"&content="+$.str.encode(content));
	$.phpok.json(url,function(rs){
		if(!rs.status){
			wait_obj.close();
			clearTimeout(time_obj);
			$.dialog.alert(rs.info);
			return false;
		}
		//跳转到后台首页
		if(rs.status == 1){
			clearTimeout(time_obj);
			wait_obj.close();
			$.dialog.tips(rs.info,function(){
				$.phpok.go(get_url('index'));
			}).lock();
			return true;
		}
		if(rs.info == 1){
			if(!wait_obj || typeof wait_obj != 'function'){
				wait_obj = $.dialog.tips('扫码成功，等待管理员确认登录',100).follow($("#qrcode")[0]);
			}else{
				wait_obj.content('扫码成功，等待管理员确认登录').time(100);
			}
		}
		time_obj = setTimeout(function(){
			qrcode_login_checking();
		},5000);
	})
}



$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
	cLanguage();
	if($("#c-language").length>0){
		var langid = $("#c-language").attr("data-lang");
		if(!langid){
			langid = $("#c-language .c-option li:eq(0)").attr('data-lang');
			update_lang(langid);
		}
	}
	//检测二维码和PC的点击
	$(".qrcode-img").click(function(){
		$("#adminlogin").hide();
		$("#admin-qrcode").show();
		$('#qrcode').html("");
		//生成新的二维码
		var url = get_url('login','qrcode');
		var t = $.dialog.tips('正在生成二维码，请稍候…',100).lock();
		$.phpok.json(url,function(rs){
			t.close();
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			var ext = "fid="+rs.info.fid+"&content="+$.str.encode(rs.info.content);
			var text = get_url("login","mobile",ext);
			$("#qrcode").qrcode(text);
			$("#qrcode-fid").val(rs.info.fid);
			$("#qrcode-content").val(rs.info.content);
			//开启监听
			qrcode_login_checking();
		});
	});
	$(".qrcode-img2").click(function(){
		$("#adminlogin").show();
		$("#admin-qrcode").hide();
		//关闭监听
		clearTimeout(time_obj);
		if(wait_obj && typeof wait_obj == 'function'){
			wait_obj.close();
		}
	})
});