/***********************************************************
	Filename: {phpok}/js/project.js
	Note	: 项目管理的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月29日
***********************************************************/
function project_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(title) title = $.trim(title);
	if(!title)
	{
		$.dialog.alert("名称不能为空");
		return false;
	}
	var identifier = $("#identifier").val();
	if(identifier) identifier = $.trim(identifier);
	if(!identifier)
	{
		$.dialog.alert("标识不能为空");
		return false;
	}
	var url = get_url("project","identifier")+"&sign="+$.str.encode(identifier);
	if(id)
	{
		url += "&id="+id;
	}
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

function project_content_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(title) title = $.trim(title);
	if(!id || id == "0" || id == "undefined")
	{
		$.dialog.alert("操作异常，请重新加载");
		return false;
	}
	if(!title)
	{
		$.dialog.alert("名称不能为空");
		return false;
	}
	return true;
}

function project_delete(id,title)
{
	var url = get_url("project","delete") + "&id="+id;
	$.dialog.confirm("确定要删除 <span class='red'>"+title+"</span> 吗？删除后其内容将会一起被清除掉",function(){
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("删除成功",function(){
				window.location.reload();
			});
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function set_status(id)
{
	var url = get_url("project","status") + '&id='+id;
	var old_value = $("#status_"+id).attr("value");
	var new_value = old_value == "1" ? "0" : "1";
	url += "&status="+new_value;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#status_"+id).removeClass("status"+old_value).addClass("status"+new_value).attr("value",new_value);
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function page_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("project","sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#taxis_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$.dialog.alert("排序更新成功",function(){
			window.location.reload();
		});
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function update_taxis(val,id)
{
	$.ajax({
		'url':get_url('project','sort','sort['+id+']='+val),
		'dataType':'json',
		'cache':false,
		'async':true,
		'beforeSend': function (XMLHttpRequest){
			XMLHttpRequest.setRequestHeader("request_type","ajax");
		},
		'success':function(rs){
			if(rs.status == 'ok')
			{
				$("#taxis_"+id).addClass('status1');
				window.setTimeout(function(){
					$("#taxis_"+id).removeClass('status1');
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

function show_module(val)
{
	if(val && val != "0")
	{
		$("#tmp_orderby_btn").html("");
		$("#module_set").show();
		var url = get_url("project","mfields") + "&id="+val;
		rs = json_ajax(url);
		if(rs.status == "ok")
		{
			var lst = rs.content;
			var c = '';
			for(var i in lst)
			{
				c += '<li><input type="button" value="'+lst[i].title+'" onclick="phpok_admin_orderby(\'orderby\',\'ext.'+lst[i].identifier+'\')"/></li>';
			}
			$("#tmp_orderby_btn").html(c);
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	}
	else
	{
		$("#module_set").hide();
	}
}

function cate_add()
{
	var url = get_url('cate',"add");
	url = $.phpok.nocache(url);
	$.dialog.open(url,{
		"title":"创建根分类",
		"width":"700px",
		"height":"300px",
		"lock":true,
		"win_max":false,
		"win_min":false,
		'move':false
	});
}

function tab_setting(val)
{
	$("#float_tab li").each(function(i){
		var name = $(this).attr("name");
		if(name == val)
		{
			$(this).removeClass("tab_out").addClass("tab_over");
			$("#"+val+"_setting").show();
		}
		else
		{
			$(this).removeClass("tab_over").addClass("tab_out");
			$("#"+name+"_setting").hide();
		}
	});
}

function set_biz()
{
	var status = $("#is_biz").attr('checked');
	if(status)
	{
		$("#use_biz_setting").show();
	}
	else
	{
		$("#use_biz_setting").hide();
	}
}

function set_post_status()
{
	var status = $("#post_status").attr('checked');
	if(status)
	{
		$("#email_set_post_status").show();
		$("li[name=f_post]").show();
	}
	else
	{
		$("#email_set_post_status").hide();
		$("li[name=f_post]").find('input').attr("checked",false);
		$("li[name=f_post]").hide();
	}
}

function set_comment_status()
{
	var status = $("#comment_status").attr('checked');
	if(status)
	{
		$("#email_set_comment_status").show();
		$("li[name=f_reply]").show();
	}
	else
	{
		$("#email_set_comment_status").hide();
		$("li[name=f_reply]").find('input').attr("checked",false);
		$("li[name=f_reply]").hide();
	}
}