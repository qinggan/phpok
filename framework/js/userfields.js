/***********************************************************
	Filename: {phpok}/js/userfields.js
	Note	: 会员自定义字段管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月4日
***********************************************************/
//编辑字段
function user_field_edit(id)
{
	var url = get_url("user","field_edit") + "&id="+id;
	$.dialog.open(url,{
		"title" : "编辑字段属性",
		"width" : "700px",
		"height" : "95%",
		"resize" : false,
		"lock" : true,
		'close'	: function(){
			direct(window.location.href);
		}
	});
}

//删除字段
function user_field_del(id,title)
{
	var qc = confirm("确定要删除字段："+title+"？删除此字段将同时删除相应的内容信息！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("user","field_delete") + "&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}
