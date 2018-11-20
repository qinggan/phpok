/**
 * 会员编辑动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年10月26日
**/

function update_user_group(id)
{
	var str = $("#group_id option:selected").attr("data-fields");
	if(!str || str == 'undefined'){
		return true;
	}
	var list = str.split(",");
	$("div[name=userext_html]").hide();
	for(var i in list){
		$("#userext_"+list[i]).show();
	}
}

$(document).ready(function(){
	layui.use(['form','laydate'],function(){
		var laydate = layui.laydate;
		var form = layui.form;
		laydate.render({
			elem: '#regtime',
			type: 'datetime'
		});
		form.on('select(usergroup)', function(data){
			update_user_group();
		});
		form.render();
	});
	
	update_user_group();
});