/**************************************************************************************************
	文件： {phpok}/js/rewrite.js
	说明： 伪静态页网址
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2015年02月03日 18时06分
***************************************************************************************************/
function add_it()
{
	var dialog = art.dialog({'title': "添加规则",'lock':true});
	$.ajax({
		'url':get_url('rewrite','set'),
		success: function (data) {
			dialog.content(data);
		},
		cache: false
	});
}
function edit_it(id,title)
{
	var dialog = art.dialog({'title': "编辑规则："+title,'lock':true});
	$.ajax({
		'url':get_url('rewrite','set','id='+id),
		success: function (data) {
			dialog.content(data);
		},
		cache: false
	});
}
function delete_it(id,title)
{
	$.dialog.confirm("确定要删除规则：<span class='red'>"+title+"</span> 吗？",function(){
		var url = get_url('rewrite','delete','id='+id);
		var rs = $.phpok.json(url);
		if(rs.status == 'ok')
		{
			$.dialog.alert("删除成功",function(){
				$.phpok.reload();
			},'succeed');
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}
function insert_input(val)
{
	var info = $("#urltype").val();
	if(info)
	{
		if(val == '.html' && info.substr((info.length-1)) == '/')
		{
			info = info.substr(0,(info.length-1));
		}
		val = info + ""+val;
	}
	$("#urltype").val(val);
}
function insert_type(type)
{
	var id = $("#id").val();
	var info = $("#urltype").val();
	if(info)
	{
		id = info + ""+id;
	}
	$("#urltype").val(id);
}
function update_urltype(type)
{
	var val = $("#id").val();
	if(val.substr(0,7) != 'project')
	{
		$("#urltype").val(val+"/");
	}
	else
	{
		$("#urltype").val('');
	}
}
