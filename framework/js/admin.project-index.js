/**
 * 项目管理首页js
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月11日
**/

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('请填写新的排序'),function(val){
			if(val != oldval){
				$.admin_project.sort(val,id);
			}
		},oldval);
	});
});