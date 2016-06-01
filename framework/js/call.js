function update_param(id,val)
{
	var url = get_url('call','cate_list');
	if(id && id != 'undefined'){
		id = $("#pid").val();
		if(id && id != 'undefined'){
			url += "&id="+id;
		}
	}
	//判断是否读分类
	var typeid = $("input[name=type_id]:checked").val();
	if(typeid != 'arclist' && typeid != 'total' && typeid != 'cate' && typeid != 'catelist' && typeid != 'subcate'){
		return true;
	}
	//异步更新分类
	$.phpok.json(url,function(data){
		if(data.status){
			var cate = data.info.cate;
			var rslist = data.info.catelist;
			var html = '';
			var space = '';
			if(cate){
				html += '<option value="'+cate.id+'">根分类：'+cate.title+'</option>';
				space = '&nbsp; &nbsp;';
			}else{
				html += '<option value="">请选择…</option>';
			}
			if(rslist){
				for(var i in rslist){
					html += '<option value="'+rslist[i].id+'"';
					if(rslist[i].id == val){
						html += ' selected';
					}
					html += '>'+space+' '+rslist[i]._space +  ' '+rslist[i].title+'</option>';
				}
			}
			$("#cateid").html(html);
			$("div[name=ext_cateid]").show();
		}else{
			$("div[name=ext_cateid]").hide();
			$("#cateid").html('<option value="0">.</option>');
		}
		//更新
		if($("input[name=type_id]:checked").val() == 'arclist'){
			end_param();
		}
	},true);
}

function load_catelist()
{
	//禁用那些module为0的option
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
	var pid = $("#pid").val();
	var cateid =$("#cateid").val();
	update_param(pid,cateid);
	//取得当前项目信息
}

function load_catelist2()
{
	//禁用那些module为0的option
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
	$("#pid").find("option[rootcate=0]").hide();
	var pid = $("#pid").val();
	var cateid =$("#cateid").val();
	update_param(pid,cateid);
}

function load_project()
{
	$("#pid").find('option').show();
}

function load_project2()
{
	$("#pid").find('option').show();
	$("#pid").find("option[module=0]").hide();
}

function load_project3()
{
	$("#pid").find('option').show();
	$("#pid").find("option[parentid=0]").hide();
}

function load_project4()
{
	$("#pid").find('option').show();
	$("#pid").find("option[parentid!=0]").hide();
}


function input_fields(val)
{
	if(val == '*'){
		$("#fields").val('*');
	}else{
		var tmp = $("#fields").val();
		if(tmp == '*'){
			$("#fields").val(val);
		}else{
			var n = tmp;
			if(tmp){
				n += ',';
			}
			n += val;
			$("#fields").val(n);
		}
	}
}
function end_param()
{
	$("div[name=ext_need_list],div[name=ext_orderby],div[name=ext_attr],div[name=ext_fields]").hide();
	var pid = $("#pid").val();
	if(!pid || pid == "undefined"){
		return true;
	}
	var url = get_url('call','arclist')+"&pid="+pid;
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			var html = $("#fields_need_default").html() + rs.content.need;
			$("#fields_need_list").html(html);
			html = $("#orderby_default").html() + rs.content.orderby;
			$("#orderby_li").html(html);
			if(rs.content.attr == 1){
				$("div[name=ext_attr]").show();
			}
			html = '<div class="button-group">';
			html += '<input type="button" value="全部字段" onclick="input_fields(\'*\')" class="phpok-btn" />';
			var lst = rs.content.rslist;
			for(var i in lst){
				html += '<input type="button" value="'+lst[i].title+'" onclick="input_fields(\''+lst[i].identifier+'\')" class="phpok-btn" />'
			}
			html += '</div>';
			$("#fields_list").html(html);
			$("div[name=ext_fields]").show();
		}else{
			$("#fields_need_list").html($("#fields_need_default").html());
			$("#orderby_li").html($("#orderby_default").html());
			
		}
		$("div[name=ext_need_list],div[name=ext_orderby]").show();
	})
}

function update_type_id(val)
{
	$("div[ext=param]").hide();
	if(!val || val == 'undefined'){
		val = $("input[name=type_id]:checked").val();
		if(!val){
			return false;
		}
	}
	var showid = $("input[name=type_id][value="+val+"]").attr('showid');
	if(!showid || showid == 'undefined'){
		return false;
	}
	var lst = showid.split(",");
	for(var i in lst){
		$("div[name=ext_"+lst[i]+"]").show();
	}
	//动态执行Ajax
	var chk_ajax = $("input[name=type_id][value="+val+"]").attr('ajax');
	if(chk_ajax && chk_ajax != 'undefined'){
		eval(chk_ajax+'()');
	}
	return true;
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

function random_string(len) {
　　len = len || 10;
　　var $chars = 'abcdefhijkmnprstwxyz';
　　var maxPos = $chars.length;
　　var pwd = '';
　　for (i = 0; i < len; i++) {
　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
　　}
　　$("#identifier").val(pwd);
}