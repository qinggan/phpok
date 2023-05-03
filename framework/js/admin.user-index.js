/**
 * 会员列表页
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License
 * @时间 2022年4月17日
**/

$(document).ready(function(){
	//渲染表头
	var total = $("#tablelist").attr("data-total");
	var psize = $("#tablelist").attr("data-psize");
	var opt = {'limit':psize};
	layui.table.init("tablelist",opt);
});