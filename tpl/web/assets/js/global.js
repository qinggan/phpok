/**
 * 新版公共脚本
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年5月5日
 * @更新 2023年5月5日
**/

/**
 * 导航菜单下拉（由 Bootstrap 自带的点击改成鼠标移过就生效）
**/
function dropdownOpen() {
    var $dropdownLi = $('li.dropdown');
    $dropdownLi.mouseover(function() {
        $(this).addClass('show').find(".dropdown-menu").addClass('show');
    }).mouseout(function() {
        $(this).removeClass('show').find(".dropdown-menu").removeClass('show');
    });
}

/**
 * 搜索框
**/
function top_search(obj)
{
	var title = $(obj).find("input[name=keywords]").val();
	if(!title){
		$.dialog.tips('请输入要搜索的关键字');
		return false;
	}
	return true;
}


/**
 * 文档加载完成后初始化
**/
$(document).ready(function(){
	$(document).off('click.bs.dropdown.data-api');
	dropdownOpen();//调用
});