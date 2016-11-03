/***********************************************************
	Filename: {phpok}js/global.admin.js
	Note	: 后台公共JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月12日
***********************************************************/
function alt_open(id,note)
{
	if(!id || id == "undefined") return false;
	if(!note || note == "undefined")
	{
		note = $("#"+id).attr("alt") ? $("#"+id).attr("alt") : $("#"+id).attr("title");
		if(!note || note == "undefined")
		{
			return false;
		}
	}
	$.dialog({
		"id": "phpok_alt",
		"title": false,
		"cancel":false,
		"padding":"10px 10px",
		"follow": document.getElementById(id),
		"content":note
	});
}

function alt_close()
{
	$.dialog.list["phpok_alt"].close();
}

//通用更新排序
function taxis(baseurl,default_value)
{
	var url = baseurl;
	if(!default_value || default_value == "undefined") default_value = "0";
	var id_string = $.input.checkbox_join();
	if(!id_string || id_string == "undefined"){
		$.dialog.alert("没有指定要更新的排序ID！");
		return false;
	}
	//取得排序值信息
	var id_list = id_string.split(",");
	var id_leng = id_list.length;
	for(var i=0;i<id_leng;i++){
		var taxis = $("#taxis_"+id_list[i]).val();
		if(!taxis) taxis = default_value;
		url += "&taxis["+id_list[i]+"]="+$.str.encode(taxis);
	}
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.dialog.alert("排序更新完成！",function(){
			$.phpok.reload();
		});
	}else{
		alert(rs.content);
		return false;
	}
}

/* 通用状态更新 */
function phpok_status(id,url)
{
	if(!url || url == "undefined" || !id) return false;
	url += "&id="+$.str.encode(id);
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		if(!rs.content) rs.content = '0';
		var oldvalue = $("#status_"+id).attr("value");
		var old_cls = "status"+oldvalue;
		$("#status_"+id).removeClass(old_cls).addClass("status"+rs.content);
		$("#status_"+id).attr("value",rs.content);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

/* 通用表单自动存储 */
// formid，要自动存储的表单ID
// type，要自动存储的类型，目前仅支持 cate list两种
// func，返回执行的函数
function autosave(formid,type,func)
{
	if(!type || type == "undefined") type = "list";
	if(!func || func == "undefined") func = "autosave_callback";
	var str = $("#"+formid).serialize();
	var url = get_url("auto") + "&__type="+type;
	//通过POST存储数据
	$.post(url,str,function(rs){func(rs);},"json");
}

/* 自动填写表单数据 */
function autofill(type)
{
	var turl = get_url("auto","read") + "&__type="+type;
	$.ajax({
		url:turl,
		cache:false,
		async:true,
		dataType:"json",
		success: function(rs){
			if(rs.status == "ok")
			{
				var list = rs.content;
				for(var key in list)
				{
					var input = $("input[name="+key+"]");
					var textarea = $("textarea[name="+key+"]");
					if(input.length>0)
					{
						input.val(list[key]);
					}
					else if(textarea.length>0)
					{
						var edit = $("textarea[name="+key+"][phpok_id=htmledit]");
						if(edit.length>0)
						{
							var my_edit = eval(key+"_editor");
							my_edit.html(list[key]);
						}
						else
						{
							textarea.val(list[key]);
						}
					}
				}
			}
		}
	});
}

//弹出图片选择窗口
function phpok_pic(id)
{
	if(!id || id == "undefined"){
		$.dialog.alert(p_lang('未指定ID'));
		return false;
	}
	var url = get_url("open","input",'id='+id);
	$.dialog.open(url,{
		title: p_lang('图片管理器'),
		lock : true,
		width: "650px",
		height: "450px"
	});
}

// 预览图片
function phpok_pic_view(id)
{
	var url = $("#"+id).val();
	if(!url || url == "undefined"){
		$.dialog.alert("图片不存在，请在表单中填写图片地址");
	}else{
		top.$.dialog({
			'title':'预览',
			'content':'<img src="'+url+'" border="0" />',
			'lock':true,
			'ok':function(){},
			'height':350,
			'width':500,
			'okVal':'关闭预览'
		});
	}
}

//弹出窗口，选择模板
function phpok_tpl_open(id)
{
	var url = get_url("tpl","open") + "&id="+id;
	$.dialog.open(url,{
		title: "模板选择",
		lock : true,
		width: "700px",
		height: "400px",
		resize: false
	});
}





function phpok_admin_orderby(id,val)
{
	$.dialog({
		"title":"排序设置",
		"content":'<div><label for="_phpok_tmp_desc_'+id+'"><input type="radio" name="_phpok_tmp_desc_asc_'+id+'" value="DESC" checked id="_phpok_tmp_desc_'+id+'"/>倒序↓，数值从高排到低，示例：Z→A，9→1</label></div><div><label for="_phpok_tmp_asc_'+id+'"><input type="radio" value="ASC" name="_phpok_tmp_desc_asc_'+id+'" id="_phpok_tmp_asc_'+id+'"/>正序↑，数值从低排到高，示例：A→Z，1→9</label></div>',
		'lock':true,
		"ok":function(){
			var desc_asc = $("input[name=_phpok_tmp_desc_asc_"+id+"]:checked").val();
			if(!desc_asc)
			{
				alert("请选择排序方式");
				return false;
			}
			val += " "+desc_asc;
			var str = $("#"+id).val();
			if(str)
			{
				str += ","+val;
			}
			else
			{
				str = val;
			}
			$("#"+id).val(str);
		}
	});
}

function goto_site(id,oldid)
{
	$.dialog.confirm(p_lang('确定要切换到网站')+"<span style='color:red;font-weight:bold;'>"+$('#top_site_id').find("option:selected").text()+"</span>",function(){
		var url = get_url("index","site") + "&id="+id.toString();
		direct(url);
	},function(){
		$("#top_site_id").val(oldid);
	});
}

//添加自定义字段
function ext_add(module)
{
	var url = get_url('ext','create','id='+$.str.encode(module));
	$.dialog.open(url,{
		'title':'创建扩展字段',
		'width':'750px',
		'height':'580px',
		'resize':false,
		'lock':true,
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal': '提交配置'
	});
	return true;
}

function ext_add2(id,module)
{
	var url = get_url("ext","add") + "&module="+module+"&id="+id;
	var rs = $.phpok.json(url);
	if(rs.status == 'ok'){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

function ext_delete(id,module,title)
{
	$.dialog.confirm('确定要删除扩展字段：<span class="red">'+title+'</span> 吗？删除后是不能恢复的！',function(){
		var url = get_url('ext','delete');
		url += "&module="+$.str.encode(module);
		url += "&id="+id;
		var rs = $.phpok.json(url);
		if(rs.status == 'ok')
		{
			autosave(module,module,auto_refresh);
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

//编辑字段
function ext_edit(id,module)
{
	var url = get_url("ext","edit",'id='+id);
	url += "&module="+$.str.encode(module);
	$.dialog.open(url,{
		"title" : "编辑扩展字段属性",
		"width" : "700px",
		"height" : "95%",
		"win_min":false,
		"win_max":false,
		"resize" : false,
		"lock" : true,
		'okVal': '提交',
		'ok': function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		}
	});
}

//前台常用JS函数封装
;(function($){
	$.admin = {
		//更换Tab设置
		tab:function(val){
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
		},
		group:function(obj){
			var val = $(obj).attr('name');
			$.each($(obj).parent().find('li'),function(i){
				var name = $(this).attr('name');
				$(this).removeClass('on');
				$("#"+name+"_setting").hide();
			});
			//显示当前的
			$(obj).addClass('on');
			$("#"+val+"_setting").show();
		}
	};
})(jQuery);

