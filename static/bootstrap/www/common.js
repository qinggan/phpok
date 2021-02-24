/**
 * 使用可视化布局时前台涉及到的页面前端脚本操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月7日
**/

$(document).ready(function(){
	//动画事件
	var is_wow = false;
	$("div[wow-action=true]").each(function(){
		var act_in = $(this).attr("wow-in");
		var act_out = $(this).attr("wow-out");
		if(act_in && act_in !='0' && act_in != 'undefined'){
			$(this).addClass("wow").addClass(act_in);
			is_wow = true;
		}
		//if(act_out && act_out !='0' && act_out != 'undefined'){
		//	$(this).addClass("wow").addClass(act_out);
		//	is_wow = true;
		//}
	});
	//启用动画，记得需要加 wow.js 和 animate 样式
	if(is_wow){
		(new WOW()).init();
	}
});