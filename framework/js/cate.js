/***********************************************************
	Filename: {phpok}/js/cate.js
	Note	: 栏目管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-06 17:42
***********************************************************/

function cate_ext()
{
	var cate_id = $("#id").val();
	url = get_url("cate","ext");
	if(cate_id)
	{
		url += "&id="+cate_id;
	}
	var msg = get_ajax(url);
	$("#ext_html").html(msg);
}

// 检测存储的表单
function cate_check()
{
	var title = $("#title").val();
	if(!title)
	{
		$.dialog.alert("分类名称不能为空！");
		return false;
	}
	var id = $("#id").val();
	var url = get_url("cate","check");
	if(id && id != "0" && id != "undefined")
	{
		url += "&id="+id;
	}
	var identifier = $("#identifier").val();
	if(!identifier || identifier == "undefined")
	{
		$.dialog.alert("标识串不能为空！");
		return false;
	}
	if(!$.str.identifier(identifier))
	{
		$.dialog.alert("标识不符合系统要求，要求仅支持：字母、数字或下划线（中划线）且首字必须为字母");
		return false;
	}
	url += "&sign="+$.str.encode(identifier);
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

function show_submit(id)
{
	//var html = '<input type="button" class="phpok-btn" value="提交" onclick="update_taxis('+id+')" />';
	//$("#taxis_submit_"+id).html(html);
}

function hide_element(id)
{
	update_taxis(id);
	//$("#taxis_submit_"+id).html('&nbsp;');
}

function update_taxis(id)
{
	val = $("#taxis_"+id).val();
	$.ajax({
		'url':get_url('cate','ajax_taxis','id='+id+'&taxis='+val),
		'dataType':'json',
		'cache':false,
		'async':true,
		'beforeSend': function (XMLHttpRequest){
			XMLHttpRequest.setRequestHeader("request_type","ajax");
		},
		'success':function(rs){
			if(rs.status == 'ok')
			{
				$("#taxis_submit_"+id).html('<span class="status1"></status1>');
				window.setTimeout(function(){
					$("#taxis_submit_"+id).html('&nbsp;');
				}, 1000);
			}
			else
			{
				$.dialog.alert(rs.content);
				return false;
			}
		}
	});
}