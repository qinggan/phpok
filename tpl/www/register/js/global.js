/**
 * 用户注册公共脚本
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月14日
**/

function dropdownOpen() {
    var $dropdownLi = $('li.dropdown');
    $dropdownLi.mouseover(function() {
        $(this).addClass('show').find(".dropdown-menu").addClass('show');
    }).mouseout(function() {
        $(this).removeClass('show').find(".dropdown-menu").removeClass('show');
    });
}


$(document).ready(function(){
	$("#vcode").phpok_vcode();
	$("#vcode").click(function(){
		$(this).phpok_vcode();
	});
	$("input[type=text],input[type=password],input[type=email],input[type=tel],select").addClass("form-control");
	$("input[type=checkbox],input[type=radio]").addClass("form-check-input");

	$(document).off('click.bs.dropdown.data-api');
	dropdownOpen();//调用
});