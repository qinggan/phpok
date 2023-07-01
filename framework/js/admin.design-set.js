/**
 * 设计器组件管理
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月12日
**/

function design_set_select_vtype(val)
{
	$("div[data-name=vtype]").hide();
	$("div[data-type=template]").hide();
	if(val == 'iframe'){
		$("div[data-id=iframe],div[data-id=width-height]").show();
	}
	if(val == 'image'){
		$("div[data-id=image],div[data-id=width-height]").show();
	}
	if(val == 'video'){
		$("div[data-id=video],div[data-id=width-height]").show();
	}
	if(val == 'calldata'){
		$("div[data-id=calldata]").show();
		$("div[data-type=template]").show();
	}
	if(val == 'editor' || val == 'code' || val == 'textarea'){
		$("div[data-type=template]").show();
	}
}

$(document).ready(function(){
	layui.form.on("select(vtype)",function(data){
		design_set_select_vtype(data.value);
	});
	var val = $("#vtype").val();
	design_set_select_vtype(val);
});