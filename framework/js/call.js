function update_param(id,val)
{
	if(!id || id == "undefined")
	{
		$("#cate_list").hide().html('<input type="hidden" name="cateid" id="cateid" value="0"/>');
		$("#cateid").val("0");
	}
	else
	{
		var url = get_url("call","cate_list") + "&id="+id;
		if(val && val != "undefined")
		{
			url += "&val="+$.str.encode(val);
		}
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$("#cate_list").html(rs.content).show();
		}
		else
		{
			$("#cate_list").html('<input type="hidden" name="cateid" id="cateid" value="0"/>').hide();
		}
	}
	//动态更换标题
	var update_change_title = false;
	var tmp_title = $("#title").val();
	if(!tmp_title)
	{
		update_change_title = true;
	}
	else
	{
		$("#pid option").each(function(i){
			var tVal = $(this).text();
			if(tVal == tmp_title) update_change_title = true;
		});
	}
	if(!id || id == "undefined") update_change_title = false;
	if(update_change_title)
	{
		var txt = $("#pid").find("option:selected").text();
		$("#title").val(txt);
	}
	update_type_id();
}

function update_type_id(val)
{
	if(!val || val == 'undefined')
	{
		val = $("#type_id").val();
	}
	//隐藏所有可配项
	var keylist = new Array('arclist','arc','cate','catelist','project','sublist','parent','fields','form','user','userlist');
	for(var i in keylist)
	{
		$("#"+keylist[i]+"_info").hide();
	}
	if(!val || val == 'undefined')
	{
		return false;
	}
	$("#"+val+"_info").show();
	if(val == 'arclist')
	{
		//取得当前项目信息
		var pid = $("#pid").val();
		if(!pid || pid == "undefined")
		{
			$("#fields_need_list").html($("#fields_need_default").html());
			$("#orderby_li").html($("#orderby_default").html());
			return true;
		}
		var url = get_url('call','arclist')+"&pid="+pid;
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			var html = $("#fields_need_default").html() + rs.content.need;
			$("#fields_need_list").html(html);
			html = $("#orderby_default").html() + rs.content.orderby;
			$("#orderby_li").html(html);
		}
		else
		{
			$("#fields_need_list").html($("#fields_need_default").html());
			$("#orderby_li").html($("#orderby_default").html());
		}
	}
	return true;
}

//不能为空字段选集
function fields_click(val)
{
	var tmp = $("#fields_need").val();
	if(tmp)
	{
		tmp = tmp+","+val;
	}
	else
	{
		tmp = val;
	}
	$("#fields_need").val(tmp);
}

function open_fields(id)
{
	var project_val = $("#pid").val();
	if(!project_val || project_val == "undefined")
	{
		$.dialog.alert("动态调用不支持字符选择框，请人工输入<br />不会编写的朋友，请登录官网查看帮助");
		return false;
	}
	var url = get_url("call","fields") +"&id="+$.str.encode(id);
	url += "&project_id="+project_val;
	$.dialog.open(url,{
		"title":"字符串选择器",
		"width" : "700px",
		"height" : "80%",
		"resize" : false,
		"lock" : true,
		"ok" : function(){
			if($.dialog.data(id))
			{
				$("#"+id).val($.dialog.data(id));
			}
		}
	});
}

function call_del(id,title)
{
	var url = get_url("call","delete") + "&id="+id;
	$.dialog.confirm("确定要删除：<span class='red'>"+title+"</span>，删除后前台关于此调用的数据将都失效！",function(){
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			direct(window.location.href);
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function check_save()
{
	var id = $("#id").val();
	if(!id)
	{
		var identifier = $("#identifier").val();
		if(!identifier)
		{
			$.dialog.alert("标识串不能为空");
			return false;
		}
		var url = get_url("call","check") + "&identifier="+$.str.encode(identifier);
		var rs = $.phpok.json(url);
		if(rs.status != "ok")
		{
			$.dialog.alert(rs.content);
			return false;
		}
	}
	var title = $("#title").val();
	if(!title)
	{
		$.dialog.alert("标题不能为空");
		return false;
	}
	return true;
}

function orderby_set(val)
{
	var str = $("#orderby").val();
	if(str)
	{
		str += ","+val;
	}
	else
	{
		str = val;
	}
	$("#orderby").val(str);
}