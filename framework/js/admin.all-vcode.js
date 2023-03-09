/**
 * 验证码配置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月26日
**/
layui.use(['form','layer'], function(){
	let form = layui.form;
	let layer = layui.layer;
	form.on('checkbox', function(data){
		if(data.elem.checked){
			$(data.elem).attr("checked",true);
		}else{
			$(data.elem).removeAttr("checked");
		}
	});
});