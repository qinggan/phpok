/***********************************************************
	Filename: {phpok}/form.js
	Note	: 自定义表单中涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-26 11:02
***********************************************************/
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
	if(!val || val == "undefined")
	{
		$("#"+id).html("").hide();
		return false;
	}
	var url = get_url("form","config") + "&id="+$.str.encode(val);
	if(eid && eid != "undefined")
	{
		url += "&eid="+eid;
	}
	if(etype && etype != "undefined")
	{
		url += "&etype="+etype;
	}
	$.ajax({
		"url" : url,
		"cache" : false,
		"dataType" : "html",
		"success" : function (rs)
		{
			if(rs && rs != "exit")
			{
				$("#"+id).html(rs).show();
			}
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
	if(is_multi && is_multi != 'undefined')
	{
		url += "&multi=1";
	}
	url += "&identifier="+$.str.encode(input);
	$.dialog.open(url,{
		"title" : title,
		"width" : "760px",
		"height" : "80%",
		"resize" : false,
		"lock" : true,
		"ok": function(){
			var data = $.dialog.data("title_data_"+input);
			if(data)
			{
				$("#"+input).val(data);
				window.eval("action_"+input+"_show()");
				//window.setTimeout("action_"++"_show()",500);
			}
		}
	});
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
		//$("#textarea_"+id+" xhe_default:first").hide();
		//$("#textarea_"+id+" CodeMirror:first").show();
		$(t).val("可视化");
	}
}

/*
 * PHPOK自定义表单中关于附件上传涉及到的JS操作
 * 最后修改时间：2014年7月29日
 * 此JS涉及到外部调用的JS函数get_url，json_ajax，$.str，$.dialog,$.parseJSON
 */
;(function($){
	$.phpok_upload = function(opts){
		var self = this;
		var defaults = {
			'multi':false, //是否多附件
			'id':'upload',
			'swf':'js/webuploader/uploader.swf',
			'server':'index.php',
			'pick':'#picker',
			'resize': false,
			'disableGlobalDnd':true,
			'fileVal':'upfile',
			'filetypes':'jpg,png,gif,jpeg',
			'runtimeOrder':'flash,html5',
			'cateid':0,
			'accept':{'title':'图片(*.jpg, *.gif, *.png)','extensions':'jpg,png,gif'}
		};
		opts.accept = {'title':opts.typeDesc,'extensions':opts.filetypes};
		this.opts = $.extend({},defaults,opts);
		if(this.opts.multi){
			this.opts.pick = this.opts.pick;
		}else{
			this.opts.pick = {'id':this.opts.pick,'multiple':false};
		}
		this.id = "#"+this.opts.id;
		this.update_status = 'ready';
		//添加动作
		this.open_action = function(val){
			var content = $(this.id).val();
			if(opts.multi){
				content = (content && content != "undefined") ? content + ","+val : val;
				var lst = $.unique(content.split(","));
				content = lst.join(',');
			}else{
				content = val;
			}
			$(this.id).val(content);
			if(this.opts.preview && this.opts.preview != 'undefined'){
				(this.opts.preview)(content);
			}else{
				this.preview_res(content);
			}
		};
		this.cateid = function(val){
			this.opts.cateid = val;
		};
		this.uploader = WebUploader.create(this.opts);
		this.uploader.on('beforeFileQueued',function(file){
			var val = (self.opts.filetypes).toLowerCase();
			var lst = val.split(',');
			if($.inArray((file.ext).toLowerCase(),lst) < 0){
				$.dialog.alert('不支持 <span class="red">'+file.ext+'</span> 类型附件上传');
				return false;
			}
		});
		//执行添加队列
		this.uploader.on('fileQueued', function( file ) {
			if(self.opts.progress && self.opts.progress != 'undefined'){
				(self.opts.progress)(file);
			}else{
				$(self.id+"_progress").append('<div id="phpok-upfile-' + file.id + '" class="phpok-upfile-list">' +
					'<div class="title">' + file.name + '（<span class="status">等待上传…</span>）</div>' +
					'<div class="progress"><span>&nbsp;</span></div>' +
					'<div class="cancel" id="phpok-upfile-cancel-'+file.id+'"></div>' + 
				'</div>' );
			}
			self.upload_state = 'ready';
			$("#phpok-upfile-"+file.id+" .cancel").click(function(){
				self.uploader.removeFile(file,true);
				$("#phpok-upfile-"+file.id).remove();
			});
		});
		this.uploader.on('uploadProgress',function(file,percent){
			var $li = $('#phpok-upfile-'+file.id),
	        $percent = $li.find('.progress span');
	        var width = $li.find('.progress').width();
	        $percent.css( 'width', parseInt(width * percent, 10) + 'px' );
	        $li.find('span.status').html('正在上传…');
		});
		this.uploader.on('uploadBeforeSend',function(block,data){
			data.cateid = self.opts.cateid;
		});
		this.uploader.on('uploadSuccess',function(file,data){
			if(data.status != 'ok'){
				if(!data.content) data.content = '上传异常';
				$.dialog.alert(data.content);
				return false;
			}
			$('#phpok-upfile-'+file.id).find('span.status').html('上传成功');
			if(self.opts.success && self.opts.success != 'undefined'){
				(self.opts.success)(file,data);
			}else{
				self.open_action(data.content.id);
			}
		});
		this.uploader.on('uploadAccept',function(file,data){
			//
		});
		this.uploader.on('uploadError',function(file,reason){
			$('#phpok-upfile-'+file.id).find('span.status').html('上传错误：<span style="color:red">'+reason+'</span>');
		});
		//上传完成，无论失败与否，3秒后删除
		this.uploader.on('uploadComplete',function(file){
			$("#phpok-upfile-"+file.id).hide(1000,function(){
				$(this).remove();
			})
		});
		//上传异常时，触发这个信息
		this.uploader.on('error',function(string){
			alert(string);
			return false;
		});
		$(this.id+"_submit").click(function(){
			//如果
			if($(this).hasClass('disabled'))
			{
				return false;
			}
			var f = $(self.id+"_progress .phpok-upfile-list").length;
			if(f<1)
			{
				alert('请选择要上传的文件');
				return false;
			}
			if(self.upload_state == 'ready' || self.upload_state == 'paused')
			{
				self.uploader.upload();
			}
			else
			{
				self.uploader.stop();
			}
		});
		
		//更新附件信息
		this.update_res = function(id){
			var title = $(self.id+"_title_"+id).val();
			if(!title)
			{
				$.dialog.alert("名称不能为空");
				return false;
			}
			var url = api_url("res","update_title_note") +"&id="+id;
			url += "&title="+$.str.encode(title);
			var note = $(this.id+"_content_"+id).val();
			if(note)
			{
				url += "&note="+$.str.encode(note);
			}
			var rs = json_ajax(url);
			if(rs.status == "ok")
			{
				alert("附件信息更新成功");
				return false;
			}
			else
			{
				alert(rs.content);
				return false;
			}
		};
		//删除附件功能
		this.del_res = function(id){
			var content = $(this.id).val();
			if(!content || content == "undefined")
			{
				return false;
			}
			if(content == id)
			{
				$(this.id).val("");
				$(this.id+"_list").html("").hide();
				return false;
			}
			var list = content.split(",");
			var newlist = new Array();
			var new_i = 0;
			for(var i=0;i<list.length;i++)
			{
				if(list[i] != id)
				{
					newlist[new_i] = list[i];
					new_i++;
				}
			}
			content = newlist.join(",");
			$(this.id).val(content);
			if(this.opts.preview && this.opts.preview != 'undefined')
			{
				(this.opts.preview)(content);
			}
			else
			{
				this.preview_res(content);
			}
		};
		//预览图片
		this.preview = function(id){
			var url = get_url("res_action","preview") + "&id="+id;
			$.dialog.open(url,{
				title: "预览",
				lock : true,
				width: "700px",
				height: "70%",
				resize: true
			});
		};
		//排序
		this.sort = function(){
			var t = [];
			$("."+this.opts.id+"_taxis").each(function(i){
				var val = $(this).val();
				var data = $(this).attr("data");
				t.push({"id":val,"data":data});
			});
			t = t.sort(function(a,b){return parseInt(a['id'])>parseInt(b['id']) ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['data'];
			}
			var val = list.join(",");
			$(this.id).val(val);
			if(this.opts.preview && this.opts.preview != 'undefined'){
				(this.opts.preview)(val);
			}else{
				this.preview_res(val);
			}
		};
		this.sort_title = function(){
			var t = [];
			$("#"+this.opts.id+"_list ._title input").each(function(i){
				var val = $(this).val();
				var data = $(this).attr("data");
				t.push({"id":val,"data":data});
			});
			t = t.sort(function(a,b){return a['id']>b['id'] ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['data'];
			}
			var val = list.join(",");
			$(this.id).val(val);
			if(this.opts.preview && this.opts.preview != 'undefined'){
				(this.opts.preview)(val);
			}else{
				this.preview_res(val);
			}
		};
		//获取列表
		this.preview_res = function(id){
			$(this.id+"_sort").hide();
			if(!id || id == "undefined")
			{
				id = $(this.id).val();
				if(!id || id == "undefined")
				{
					$(this.id+"_list").hide(1000,function(){
						$(this).html('');
					});
					return false;
				}
			}
			var url = api_url("res","idlist") + "&id="+$.str.encode(id);
			var optsid = this.opts.id;
			$.phpok.json(url,function(rs){
				if(rs.status != 'ok'){
					$.dialog.alert(rs.content);
					return false;
				}
				var list = rs.content;
				var total = count(list);
				var html = '<div class="_elist">';
				var t = 1;
				var tmp = id.split(",");
				for(var i in tmp){
					if(!list[tmp[i]] || list[tmp[i]] == 'undefined' || !list[tmp[i]]['ico']){
						continue;
					}
					var info = list[tmp[i]];
					var cls = t == total ? "_line_end" : "_line";
					html += '<div class="'+cls+'"><table><tr>';
					html += '<td class="img"><img src="'+info.ico+'" width="100px" height="100px" /></td>';
					html += '<td valign="top">';
					html += '<div class="_title" style="width:450px;margin-bottom:5px;"><input type="text" id="'+optsid+'_title_'+info.id+'" value="'+info.title+'" class="_input" placeholder="名称" data="'+info.id+'"></div>';
					html += '<div class="_note" style="width:450px;margin-bottom:5px;"><textarea id="'+optsid+'_content_'+info.id+'" class="_textarea" placeholder="备注">'+info.note+'</textarea></div>';
					html += '<div class="ext_action" style="width:450px;">';
					html += '<button type="button" class="_btn" onclick="obj_'+optsid+'.update_res('+info.id+')">更新附件信息</button>';
					html += '<button type="button" class="_btn" onclick="obj_'+optsid+'.preview('+info.id+')">预览</button>';
					html += '<button type="button" class="_btn" onclick="obj_'+optsid+'.del_res('+info.id+')">删除</button>';
					if(total > 1){
						html += '<input type="text" class="_taxis '+optsid+'_taxis" value="'+t+'" data="'+info.id+'" />';
					}
					html += '</div></td>';
					html += '</tr></table></div>';
					t++;
				}
				html += '</div>';
				$(self.id+"_list").html(html).show();
				if(total>1){
					$(this.id+"_sort").show();
				}
			});
		}
	};
})(jQuery);

