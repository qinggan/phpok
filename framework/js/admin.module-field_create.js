/**
 * 添加字段涉及到的样式处理
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License
 * @时间 2023年9月6日
 * @更新 2023年9月6日
**/

function field_form_opt(val,eid)
{
	if(!val || val == "undefined"){
		$("#form_type_ext").html('').hide();
		return false;
	}
	var url = get_url("form","config") + "&id="+$.str.encode(val);
	if(eid && eid != "undefined"){
		url += "&eid="+eid;
	}
	url += "&etype=fields";
	var html = get_ajax(url);
	if(html && html != 'exit'){
		$("#form_type_ext").html(html).show();
	}
}

$(document).ready(function(){
	layui.use('form',function(){
		var form = layui.form;
		form.on('select(form_type)',function(data){
			$._configForm.option(data.value,'form_type_ext',0,'module');
		});
	})
});