/***********************************************************
	Filename: phpok/js/fields.js
	Note	: 字段管理中涉及的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-26 17:07
***********************************************************/
// 验证标识串
function check_identifier(is_alert)
{
	var c = $("#identifier").val();
	if(!c)
	{
		$("#identifier_note").addClass("error").html("标识串不能为空！");
		if(is_alert) alert("标识串不能为空！");
		return false;
	}
	//验证标识串是否符合要求
	if(!$.str.identifier(c))
	{
		alert("标识串不符合系统要求，要求仅支持：字母、数字或下划线且首字必须为字母");
		return false;
	}
	//通过服务端验证数据是否存在
	var url = get_url("ajax","exit","filename=field_identifier") + "&identifier="+c;
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		$("#identifier_note").addClass("error").html(rs.content);
		if(is_alert) alert(rs.content);
		return false;
	}
	$("#identifier_note").removeClass("error").html("");
	return true;
}

// 验证标题
function check_title(is_alert)
{
	var c = $("#title").val();
	if(!c)
	{
		$("#title_note").addClass("error").html("名称不能为空！");
		if(is_alert) alert("名称不能为空！");
		return false;
	}
	$("#title_note").removeClass("error").html("");
	return true;
}

/* 样式属性操作 */
function set_style(act)
{
	var value = $("#form_style").val();
	//去除空格信息
	if(value && value != "undefined")
	{
		value = value.replace(/ /g,"");
		value = value.toLowerCase();
	}
	if(act == "bold")
	{
		var regexp = new RegExp("font-weight:bold;");
		if(regexp.test(value))
		{
			value = value.replace("font-weight:bold;","");
			$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		}
		else
		{
			value += "font-weight:bold;";
			$("#css_edit_bold").removeClass("btn").addClass("btn_selected");
		}
		$("#form_style").val(value);
	}
	else if(act == "width")
	{
		var regexp = new RegExp("width:");
		if(regexp.test(value))
		{
			value = value.replace(/width:\w+;/,"");
			$("#css_edit_width").removeClass("btn_selected").addClass("btn");
			$("#form_style").val(value);
		}
		else
		{
			apprise('设置宽度', {'input':true,'textOk':'提交','textCancel':'取消'},function(r){
				if(r && r != false)
				{
					var t_value = r.replace("px","");
					value += "width:"+t_value+"px;";
					$("#form_style").val(value);
					$("#css_edit_width").removeClass("btn").addClass("btn_selected");
				}
			});
		}
	}
	else if(act == "height")
	{
		var regexp = new RegExp("height:");
		if(regexp.test(value))
		{
			value = value.replace(/height:\w+;/,"");
			$("#css_edit_height").removeClass("btn_selected").addClass("btn");
			$("#form_style").val(value);
		}
		else
		{
			apprise('设置高度', {'input':true,'textOk':'提交','textCancel':'取消'},function(r){
				if(r && r != false)
				{
					var t_value = r.replace("px","");
					value += "height:"+t_value+"px;";
					$("#form_style").val(value);
					$("#css_edit_height").removeClass("btn").addClass("btn_selected");
				}
			});
		}
	}
}

function load_style()
{
	var value = $("#form_style").val();
	if(value && value != "undefined")
	{
		//判断是否有高度属性
		var regexp = new RegExp("height:");
		if(regexp.test(value))
		{
			$("#css_edit_height").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_height").removeClass("btn_selected").addClass("btn");
		}
		//判断是否有宽度属性
		var regexp = new RegExp("width:");
		if(regexp.test(value))
		{
			$("#css_edit_width").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_width").removeClass("btn_selected").addClass("btn");
		}
		//判断是否有加粗
		var regexp = new RegExp("font-weight:");
		if(regexp.test(value))
		{
			$("#css_edit_bold").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		}
	}
	else
	{
		$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		$("#css_edit_width").removeClass("btn_selected").addClass("btn");
		$("#css_edit_height").removeClass("btn_selected").addClass("btn");
	}
}

function show_form_opt(val)
{
	$("#form_opt_html").hide();
	$("#form_btn_html").hide();
	$("#form_edit_html").hide();
	//要显示的值
	var list = new Array("radio","checkbox","select","select_multiple","related_multiple","related_single");
	var is_list = false;
	for(var i=0;i<list.length;i++)
	{
		if(list[i] == val)
		{
			is_list = true;
		}
	}
	if(is_list)
	{
		$("#form_opt_html").show();
	}
	if(val == "text")
	{
		$("#form_btn_html").show();
	}
	if(val == "html_editor")
	{
		$("#form_edit_html").show();
	}
}

// 检查添加的字段属性
function field_add_check(tbl_prefix,id)
{
	//判断名称是否为空
	var chk_title = check_title(true);
	if(!chk_title) return false;

	if(!id || id == "undefined")
	{
		// 检测标识串
		var chk_identifier = check_identifier(true);
		if(!chk_identifier) return false;
	}

	//检测存储类型
	var field_type = $("#field_type").val();
	var tbl = $("#field_tbl").val();
	if(field_type == "longtext" && (tbl == "ext" || tbl == "blob"))
	{
		alert("目标数据表存储与设置的字段类型不一致！超长文本请存储到："+tbl_prefix+"list_content 或 自定义创建 表中！");
		return false;
	}
	else if(field_type == "longblob" && (tbl == "ext" || tbl == "content"))
	{
		alert("目标数据表存储与设置的字段类型不一致！二进制信息请存储到："+tbl_prefix+"list_blob 或 自定义创建 表中！");
		return false;
	}
	else if(field_type != "longblob" && tbl == "blob")
	{
		alert("选择有错误，二进制信息不能存储到表："+tbl_prefix+"list_blob 中！");
		return false;
	}

	return true;
}

//更新排序
function update_taxis()
{
	var url = get_url("fields","taxis");
	var id_string = $.input.checkbox_join();
	if(!id_string || id_string == "undefined")
	{
		alert("没有指定要更新的排序ID！");
		return false;
	}
	//取得排序值信息
	var id_list = id_string.split(",");
	var id_leng = id_list.length;
	for(var i=0;i<id_leng;i++)
	{
		var taxis = $("#taxis_"+id_list[i]).val();
		if(!taxis) taxis = "255";
		url += "&taxis["+id_list[i]+"]="+$.str.encode(taxis);
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		alert("排序更新完成！");
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

function field_del(id,title)
{
	var qc = confirm("确定要删除字段："+title+" ？ 删除后，已投入使用的字段不受此影响！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("fields","delete");
	url += "&id="+id;
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

function fields_goto(val)
{
	var url = get_url("fields");
	if(val && val != 'undefined')
	{
		url += "&type="+val;
	}
	direct(url);
}
