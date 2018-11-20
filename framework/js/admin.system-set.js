/**
 * 系统菜单操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月15日
**/

//添加一行
function add_trtd()
{
	var count = $("#popedom tr").length;
	var n_id = "tbl_"+(count+1).toString();
	var html = '<tr id="'+n_id+'">';
	html += '<td align="center"><input type="text" name="popedom_title_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="text" name="popedom_identifier_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="text" name="popedom_taxis_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="button" value="删除" class="layui-btn layui-btn-xs layui-btn-danger"  onclick="del_trtd(\''+n_id+'\')" /></td>';
	html += '</tr>';
	$("#popedom").append(html);
}

function del_trtd(id)
{
	$("#"+id).remove();
}

function popedom_del(id)
{
	//删除权限
	var url = get_url("system","delete_popedom")+"&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#popedom_"+id).remove();
		return true;
	}
	else
	{
		if(!rs.content) rs.content = "删除失败";
		$.dialog.alert(rs.content);
		return false;
	}
}

$(document).ready(function() {
	$(".dropdown dt").click(function() {
		$(".dropdown dd ul").toggle();
	});
				
	$(".dropdown dd ul li").click(function() {
		var text = $(this).html();
		$(".dropdown dt span").html(text);
		$(".dropdown dd ul").hide();
		$("#icon").val($(this).find("span.value").html());
	});
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if (! $clicked.parents().hasClass("dropdown"))
			$(".dropdown dd ul").hide();
	});
});