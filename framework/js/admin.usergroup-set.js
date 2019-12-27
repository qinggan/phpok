/**
 * 会员组渲染
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月11日
**/
$(document).ready(function(){
	layui.use('form', function () {
		var form = layui.form;
		form.on('radio(register_status)',function(e){
			if(e.value == 'email' || e.value == 'mobile'){
				$("#register_status_notice").show();
			}else{
				$("#register_status_notice").hide();
			}
		});
	});
});