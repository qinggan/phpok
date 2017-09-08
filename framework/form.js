/**
 * 自定义表单中涉及到的JS操作
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年03月22日
**/

function phpok_form_password(id,len)
{
	var list = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	if(!len || len == "undefined") len = 8;
	var rand = "";
	for(var i = 0;i<len;i++)
	{
		var num = Math.floor(Math.random()*36+0);
		rand = rand + list[num];
	}
	var htm = "随机密码："+rand;
	$("#"+id+"_html").html(htm);
	$("#"+id).val(rand);
}

//表单扩展按钮
//btn，类型
function phpok_btn_action(btn,id)
{
	if(btn == "image")
	{
		if(!id || id == "undefined")
		{
			$.dialog.alert("未指定ID");
			return false;
		}
		var url = get_url("open","input") + "&ext="+$.str.encode("png,jpg,gif,jpeg,bmp")+"&id="+id;
		$.dialog.open(url,{
			title: "图片管理器",
			lock : true,
			width: "80%",
			height: "70%",
			resize: false
		});
	}
}

function phpok_btn_view(btn,id)
{
	if(btn == "image")
	{
		var url = $("#"+id).val();
		if(!url || url == "undefined")
		{
			$.dialog.alert("图片不存在，请在表单中填写图片地址");
		}
		else
		{
			$.dialog({
				"title":"预览",
				"content": '<img src="'+url+'" border="0" />',
				"lock":true
			});
		}
	}
}

//清空
function phpok_btn_clear(btn,id)
{
	$("#"+id).val("");
}

function _phpok_form_opt(val,id,eid,etype)
{
	if(!val || val == "undefined"){
		$("#"+id).html("").hide();
		return false;
	}
	var url = get_url("form","config") + "&id="+$.str.encode(val);
	if(eid && eid != "undefined"){
		url += "&eid="+eid;
	}
	if(etype && etype != "undefined"){
		url += "&etype="+etype;
	}
	$.phpok.ajax(url,function(rs){
		if(rs && rs != 'exit'){
			$("#"+id).html(rs).show();
		}
	});
}

function phpok_btn_editor_picture(id)
{
	var url = get_url("edit","picture") + "&input="+id;
	$.dialog.open(url,{
		"title" : "图片库",
		"width" : "760px",
		"height" : "80%",
		"resize" : false,
		"lock" : true
	});
}

function phpok_btn_editor_file(id)
{
	var url = get_url("edit","file") + "&input="+id+"&nopic=1";
	$.dialog.open(url,{
		"title" : "附件资源",
		"width" : "760px",
		"height" : "80%",
		"resize" : false,
		"lock" : true
	});
}

function phpok_btn_editor_video(id)
{
	var url = get_url("edit","video") + "&input="+id+"&nopic=1";
	$.dialog.open(url,{
		"title" : "添加影音",
		"width" : "760px",
		"height" : "80%",
		"resize" : false,
		"lock" : true
	});
}

//删除单个主题关联
function phpok_title_delete_single(id)
{
	$("#"+id).val("");
	$("#title_"+id).hide();
	$("#phpok-btn-"+id+"-delete").hide();
}

//删除多个主题关联
function phpok_title_delete(id,val)
{
	if(val && val != "undefined")
	{
		//移除DIV值
		$("#"+id+"_div_"+val).remove();
		//移除值
		var c = $("#"+id).val();
		if(c == "" || c == "undefined")
		{
			$("#"+id+"_div").hide();
			$("#"+id+"_button_checkbox").hide();
			$("#"+id).val("");
			return true;
		}
		var clist = c.split(",");
		var n_list = new Array();
		var m = 0;
		for(var i=0;i<clist.length;i++)
		{
			if(clist[i] != val)
			{
				n_list[m] = clist[i];
				m++;
			}
		}
		if(n_list.length<1)
		{
			$("#"+id+"_div").hide();
			$("#"+id+"_button_checkbox").hide();
			$("#"+id).val("");
		}
		else
		{
			$("#"+id).val(n_list.join(","));
		}
		return true;
	}
	val = $.input.checkbox_join(id+"_div");
	if(!val || val == "undefined")
	{
		$.dialog.alert("请选择要删除的信息");
		return false;
	}
	var lst = val.split(",");
	for(var i=0;i<lst.length;i++)
	{
		phpok_title_delete(id,lst[i]);
	}
	return true;
}

//选择主题关联
function phpok_title_select(project_id,is_multi,title,input)
{
	var url = get_url("inp","title")+"&project_id="+$.str.encode(project_id);
	if(is_multi && is_multi != 'undefined'){
		url += "&multi=1";
		url += "&identifier="+$.str.encode(input);
		$.dialog.open(url,{
			"title" : title,
			"width" : "760px",
			"height" : "80%",
			"resize" : false,
			"lock" : true,
			"ok": function(){
				var data = $.dialog.data("title_data_"+input);
				if(data){
					$("#"+input).val(data);
					window.eval("action_"+input+"_show()");
				}
			}
		});
	}else{
		url += "&identifier="+$.str.encode(input);
		$.dialog.open(url,{
			"title" : title,
			"width" : "760px",
			"height" : "80%",
			"resize" : false,
			"lock" : true
		});
	}
}

function phpok_user_delete(id,val)
{
	//移除DIV值
	$("#"+id+"_div_"+val).remove();
	//移除值
	var c = $("#"+id).val();
	if(c == "" || c == "undefined")
	{
		$("#"+id+"_div").html("");
		$("#"+id).val("");
		return true;
	}
	var clist = c.split(",");
	var n_list = new Array();
	var m = 0;
	for(var i=0;i<clist.length;i++)
	{
		if(clist[i] != val)
		{
			n_list[m] = clist[i];
			m++;
		}
	}
	if(n_list.length<1)
	{
		$("#"+id+"_div").html("");
		$("#"+id).val("");
	}
	else
	{
		$("#"+id).val(n_list.join(","));
	}
	return true;
}

/* PHPOK编辑器扩展按钮属性 */
function phpok_edit_type(id)
{
	var t = "#sMode_"+id;
	if($(t).val() == "可视化")
	{
		$(eval("pageInit_"+id+"()"));
		$(t).val("源代码");
	}
	else
	{
		$("#"+id).xheditor(false);
		eval("CodeMirror_PHPOK_"+id+"()");
		$(t).val("可视化");
	}
}

function phpok_form_upload_attr_cate_id()
{
	var obj = $("select#cate_id").find("option:selected");
	var dataType = obj.attr('data-type');
	var name = $("#upload_name").val();
	var type = $("#upload_type").val();
	if(!dataType || dataType == 'undefined'){
		if(name == '' || name == 'undefined'){
			$("#upload_name").val('图片');
		}
		if(type == '' || type == 'undefined'){
			$("#upload_type").val('jpg,png,gif');
		}
	}else{
		if(name == '' || name == 'undefined'){
			$("#upload_name").val(obj.text());
		}
		if(type == '' || type == 'undefined'){
			$("#upload_type").val(dataType);
		}
	}
	return true;
}


;(function($){
	
	var config = {
		'id':'phpok',
		'content':'',
		'url':'',
		'filetype':'jpg,png,gif'
	};
	var form = {
		init:function(opts)
		{
			config = $.extend({},config,opts);
			if(config.total<1){
				config.total = 10;
			}
			return form;
		},
		upload_cate_create:function(id)
		{
			
		}
	};
	$.phpokform = {
		upload_cate_create:function(id,name,filetypes){
			$.dialog.prompt(p_lang('请输入分类名称'),function(val){
				if(!val){
					$.dialog.alert(p_lang('分类名称不能为空'));
					return false;
				}
				var url = config.url;
				var url = get_url('rescate','qcreate','title='+$.str.encode(val)+"&name="+$.str.encode(name)+"&filetypes="+$.str.encode(filetypes));
				$.phpok.json(url,function(data){
					if(data.status){
						var obj = $("select[name="+id+"_cateid]");
						obj.append("<option value='"+data.info+"'>"+val+"</option>");
						obj.find("option[value="+data.info+"]").attr("selected",true);
					}else{
						$.dialog.alert(data.info);
						return false;
					}
				});
			},'');
		},
		param_type_setting:function(val,id){
			var old = $("#"+id).val();
			if(old){
				val = old+","+val;
			}
			$("#"+id).val(val);
		},
		param_type_set:function(v){
			if(v == 1){
				$("#p_name_type_html").show();
			}else{
				$("#p_name_type_html").hide();
			}
		}
	};
})(jQuery);


