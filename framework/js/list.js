/***********************************************************
	Filename: {phpok}/js/list.js
	Note	: 内容管理里的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-21 11:13
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
	return true;
}



function content_del(id)
{
	$.dialog.confirm("确定要删除主题ID：<span class='red'>"+id+"</span> 的信息吗？<br />删除后是不能恢复的？",function(){
		var url = get_url("list","del") +"&id="+id;
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("主题删除成功",function(){
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

function tab_id(id)
{
	$("#float_tab li").each(function(i){
		if(i == id)
		{
			$(this).removeClass("tab_out").addClass("tab_over");
			$("#content_"+id).show();
		}
		else
		{
			$(this).removeClass("tab_over").addClass("tab_out");
			$("#content_"+i).hide();
		}
	});
}

// 显示高级属性配置
function show_advanced()
{
	if($("#advanced").is(":hidden"))
	{
		$("#advanced").show();
	}
	else
	{
		$("#advanced").hide();
	}
}

function project_delete(id)
{
	var title = $("#txt_"+id).html();
	var url = $("#delurl_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有错误，请检查");
		return false;
	}
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

function project_config(id)
{
	var url = $("#config_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有错误，请检查");
		return false;
	}
	direct(url);
}

function project_content(id)
{
	var url = $("#content_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有错误，请检查");
		return false;
	}
	direct(url);
}

//批量审核
function set_status(id)
{
	var url = get_url("list","content_status") + '&id='+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		if(!rs.content) rs.content = '0';
		var oldvalue = $("#status_"+id).attr("value");
		var old_cls = "status"+oldvalue;
		$("#status_"+id).removeClass(old_cls).addClass("status"+rs.content).attr("value",rs.content);
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

//批量排序
function set_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("list","content_sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#sort_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		window.location.reload();
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
		'url':get_url('list','content_sort','sort['+id+']='+val),
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

//批量删除
function set_delete()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要删除的主题");
		return false;
	}
	$.dialog.confirm("确定要删除选定的主题吗？<br />删除后是不能恢复的？",function(){
		var url = get_url("list","del") +"&id="+$.str.encode(ids);
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("主题删除成功",function(){
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

function show_order()
{
	if($("#page_sort").is(":hidden"))
	{
		$("#page_sort").show();
	}
	else
	{
		$("#page_sort").hide();
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
	var url = get_url("list","sort");
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

function plus_price()
{
	var m = 1;
	$("#ext_price tr").each(function(i){
		if(i > 1)
		{
			m++;
		}
	});
	var html = '<tr id="ext_price_'+m+'"><td><input type="text" name="price_title[]" value="" /></td><td><input type="text" name="qty[]" value="" class="short" /></td><td><input type="text" name="price[]" value="" /></td><td><input type="button" value="-" onclick="minus_price('+m+')" class="btn" /></td></tr>';
	var t = m - 1;
	$("#ext_price_"+t).after(html);
}

function minus_price(id)
{
	$("#ext_price_"+id).remove();
}

function preview_attr(id)
{
	var url = get_url("res_action","preview") + "&id="+id;
	$.dialog.open(url,{
		title: "预览",
		lock : true,
		width: "700px",
		height: "70%",
		resize: true
	});
}

function update_select()
{
	var val = $("#list_action_val").val();
	if(val.substr(0,5) == 'attr:'){
		$("#attr_set_li").show();
	}else{
		$("#attr_set_li").hide();
	}
	if(val.substr(0,5) == 'cate:'){
		$("#cate_set_li").show();
	}else{
		$("#cate_set_li").hide();
	}
}

function set_admin_id(id)
{
	var url = get_url('workflow','title','id='+id);
	$.dialog.open(url,{
		'title':p_lang('指派管理员维护'),
		'lock':true,
		'width':'500px',
		'height':'300px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe还没加载完毕呢'));
				return false;
			};
			return iframe.save();
		},
		'cancel':function(){
			return true;
		}
	});
}

function list_action_exec()
{
	var ids = $.input.checkbox_join();
	if(!ids){
		$.dialog.alert(p_lang('未指定要操作的主题'));
		return false;
	}
	var val = $("#list_action_val").val();
	if(!val || val == ''){
		$.dialog.alert(p_lang('未指定要操作的动作'),'','error');
		return false;
	}
	if(val == 'appoint'){
		set_admin_id(ids);
		return false;
	}
	if(val == 'delete'){
		set_delete();
		return false;
	}
	if(val == 'sort'){
		set_sort();
		return false;
	}
	//执行批量审核通过
	if(val == 'status' || val == 'unstatus' || val == 'show' || val == 'hidden'){
		var url = get_url('list','execute','ids='+$.str.encode(ids)+"&title="+val);
	}else{
		var tmp = val.split(':');
		if(tmp[1] && tmp[0] == 'attr'){
			var type = $("#attr_set_val").val();
			url = get_url('list','attr_set','ids='+$.str.encode(ids)+'&val='+tmp[1]+'&type='+type);
		}else{
			var type = $("#cate_set_val").val();
			var url = get_url('list',"move_cate")+"&ids="+$.str.encode(ids)+"&cate_id="+tmp[1]+"&type="+type;
		}
	}
	$.dialog.tips('正在执行操作，请稍候…');
	var rs = $.phpok.json(url);
	if(rs.status == 'ok'){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}
