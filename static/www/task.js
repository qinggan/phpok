/**
 * 定时通知
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2022年2月19日
**/

/**
 * 加载定时通知
**/
$(document).ready(function(){
	$.phpok.json(api_url('task'),function(){
		return true;
	});
});