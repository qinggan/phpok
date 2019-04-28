/**
 * 管理员信息修改
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月07日
**/

;(function($){
	$.admin_me = {
		pass_submit:function()
		{
			var oldpass = $("#oldpass").val();
			if(!oldpass){
				$.dialog.alert(p_lang('旧密码不能为空'));
				return false;
			}
			var newpass = $("#newpass").val();
			var chkpass = $("#chkpass").val();
			var vpass = $("#vpass").val();
			if(!newpass && !vpass){
				$.dialog.alert(p_lang('新密码或二次密码至少有一项不能为空'));
				return false;
			}
			if(newpass && newpass != chkpass){
				$.dialog.alert(p_lang('两次输入的密码不一致'));
				return false;
			}
			if(newpass && oldpass == newpass){
				$.dialog.alert(p_lang('新旧密码是一样的，不能执行此操作'));
				return false;
			}
			if(vpass && oldpass && oldpass == vpass){
				$.dialog.alert(p_lang('二次密码不能和旧密码一样'));
				return false;
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('me','pass_submit'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('修改操作成功，下次登录或二次验证请使用新设置的密码'));
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		setting_submit()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('me','submit'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						if(rs.info == 1){
							$.dialog.alert(p_lang('管理员信息修改成功，请重新登录'),function(){
								$.phpok.go(get_url('logout'));
							},'succeed');
							return true;
						}
						$.dialog.tips(p_lang('管理员信息操作成功'));
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		}
	}
})(jQuery);