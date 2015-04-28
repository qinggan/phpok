/***********************************************************
	Filename: {phpok}/js/project.js
	Note	: 项目管理的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月29日
***********************************************************/
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

function cate_add(title)
{
	var url = get_url('cate',"add");
	$.dialog.open(url,{
		"title":title,
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