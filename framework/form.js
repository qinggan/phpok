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
	$("#layui-btn-"+id+"-delete").hide();
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
	var url = get_url("inp","title","project_id="+$.str.encode(project_id));
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

function go_to_page_action()
{
	var page = $("#go_to_page").val();
	if(!page){
		$.dialog.alert('请输入要跳转的页码');
		return false;
	}
	page = parseInt(page);
	if(page<1){
		page = 1;
	}
	var url = window.location.href;
	url = url.replace(/&pageid=\d+/g,"");
	url += "&pageid="+$.str.encode(page);
	$.phpok.go(url);
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
		//图片预览
		upload_preview:function(id)
		{
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':p_lang('预览附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':true,
				'button': [{
					'name': p_lang('下载原文件'),
					'callback': function () {
						$.phpok.open(get_url('res','download','id='+id));
						return false;
					},
				}],
				'okVal':p_lang('关闭'),
				'ok':true
			});
		},
		upload_update:function(id)
		{
			$.dialog.open(get_url('upload','editopen','id='+id),{
				'title':p_lang('编辑附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':p_lang('提交'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'cancelVal':p_lang('取消修改'),
				'cancel':function(){}
			});
		},
		upload_delete:function(identifier,id)
		{
			var fid = identifier;
			if(fid.substr(0,1) != '#' && fid.substr(0,1) != '.'){
				fid = '#'+fid;
			}
			var content = $(fid).val();
			if(!content || content == "undefined"){
				return true;
			}
			//删除单个附件
			if(content == id){
				$(fid).val("");
				$(fid+"_list").fadeOut().html('');
				this.upload_remote_delete(identifier,id);
				return true;
			}
			var list = content.split(",");
			var newlist = new Array();
			var new_i = 0;
			for(var i=0;i<list.length;i++){
				if(list[i] != id){
					newlist[new_i] = list[i];
					new_i++;
				}
			}
			content = newlist.join(",");
			$(fid).val(content);
			this.upload_remote_delete(identifier,id);
			this.upload_showhtml(identifier,true);
		},
		upload_showhtml:function(identifier,multiple)
		{
			var self = this;
			var fid = identifier;
			if(fid.substr(0,1) != '#' && fid.substr(0,1) != '.'){
				fid = '#'+fid;
			}
			var id = $(fid).val();
			if(!id){
				$(fid+"_list").html('').fadeOut();
				return false;
			}
			var url = get_url('upload','thumbshow','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status != 'ok'){
					$(fid).val('');
					$(fid+"_list").html('').fadeOut();
					return true;
				}
				var html = '';
				var index_i = 1;
				for(var i in rs.content){
					html += self.upload_html(identifier,rs.content[i],index_i,multiple);
					index_i++;
				}
				$(fid+"_list").html(html).show();
				if(!html){
					$(fid+"_list").html('').fadeOut();
					$(fid+"_sort").hide();
				}else{
					if(multiple){
						$(fid+"_sort").show();
					} else {
						$(fid+"_sort").hide();
					}
				}
				return true;
			});
		},
		upload_html:function(identifier,rs,i,multiple)
		{
			var fid = identifier;
			if(fid.substr(0,1) != '#' && fid.substr(0,1) != '.'){
				fid = '#'+fid;
			}
			var html = '<div class="'+identifier+'_thumb" name="_elist">';
			if(multiple){
				html += '<div class="sort"><input type="text" class="taxis" value="'+i+'" data="'+rs.id+'" title="'+rs.title+'" onclick="$(this).select()" tabindex="'+i+'" /></div>';
			}
			html += '<div style="text-align:center;"><img src="'+rs.ico+'" width="100" height="100" /></div>';
			html += '<div class="file-action" style="text-align:center;"><div class="button-group">';
			html += '	<input type="button" value="'+p_lang('修改')+'" class="phpok-btn" onclick="$.phpokform.upload_update(\''+rs.id+'\')" />';
			html += '	<input type="button" value="'+p_lang('预览')+'" class="phpok-btn" onclick="$.phpokform.upload_preview(\''+rs.id+'\')" />';
			html += '	<input type="button" value="'+p_lang('删除')+'" class="phpok-btn" onclick="$.phpokform.upload_delete(\''+identifier+'\',\''+rs.id+'\')" /></div>';
			html += '</div></div>';
			html += '</div>';
			return html;
		},
		upload_remote_delete:function(identifier,id)
		{
			var tmp = $.phpok.data('upload-'+identifier)
			if(!tmp || tmp == 'undefined'){
				return true;
			}
			var delete_status = false;
			if(tmp != id){
				var list = tmp.split(',');
				var newlist = new Array();
				var new_i = 0;
				for(var i=0;i<list.length;i++){
					if(list[i] != id){
						newlist[new_i] = list[i];
						new_i++;
					}else{
						delete_status = true;
					}
				}
				content = newlist.join(",");
				$.phpok.data('upload-'+identifier,content);
			} else {
				delete_status = true;
				$.phpok.undata('upload-'+identifier);
			}
			if(delete_status){
				var url = get_url('upload','delete','id='+id);
				$.phpok.json(url,function(){
					return true;
				});
			}
		},
		upload_select:function(identifier,cate_id,multiple)
		{
			var ml = (multiple && multiple != 'undefined' && multiple != 'false') ? 1 : 0;
			var url = get_url('open','upload','id='+identifier+"&multiple="+ml);
			if(cate_id && cate_id != 'undefined'){
				url += "&cate_id="+cate_id;
			}
			var t = "{$_rs.is_multiple ? 'true' : 'false'}";
			var old = $("#"+identifier).val();
			var doc_width = $(document).width();
			if(ml == 1){
				if(old){
					$.phpok.data('select-'+identifier,old);
				}
				$.dialog.open(url,{
					'title': p_lang('资源管理器'),
					'lock' : true,
					'width': '64%',
					'height': '80%',
					'ok': true,
					'okVal':p_lang('关闭')
				});
				return true;
			}
			if(old){
				url += "&selected="+old;
			}
			$.dialog.open(url,{
				'title':p_lang('资源管理器'),
				'width': '64%',
				'height': '80%',
				'lock' : true
			});
		},
		upload_sort:function(identifier,type)
		{
			var t = [];
			$("#"+identifier+"_list .taxis").each(function(i){
				if(type == 'title'){
					var val = $(this).attr('title');
				}else{
					var val = $(this).val();
				}
				var data = $(this).attr("data");
				t.push({"id":val,"data":data});
			});
			t = t.sort(function(a,b){
				return parseInt(a['id']) > parseInt(b['id']) ? 1 : -1
			});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['data'];
			}
			var val = list.join(",");
			$("#"+identifier).val(val);
			this.upload_showhtml(identifier,true);
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
				$("#p_val_type_html").show();
			}else{
				$("#p_val_type_html").hide();
			}
		},

		/**
		 * 文本框旁边的日期按钮控件
		**/
		laydate_button:function(id,type)
		{
			layui.use('laydate',function(){
				var laydate = layui.laydate;
				laydate.render({
					elem:'#'+id,
					type:type,
					show: true,
					trigger: 'click',
					closeStop: '#btn_'+id+'_'+type
				});
			});
		},

		/**
		 * 格式化日期
		**/
		laydate_format:function(id,type)
		{
			var info = $("#"+id).val();
			if(!info){
				return false;
			}
			info = info.replace("年",'-');
			info = info.replace("月",'-');
			info = info.replace("日",'');
			info = info.replace("时",'-');
			info = info.replace("分",'-');
			info = info.replace("秒",'');
			if(type == 'date'){
				var tmp = info.replace('/','-');
				if(tmp != info){
					var list = info.split('/');
					if((list[0]).length != 4){
						info = list[2]+'-'+list[0]+'-'+list[1];
					}
				}
			}
			if(type == 'datetime'){
				info = info.replace('/','-');
			}
			$("#"+id).val(info);
			return true;
		},
		/**
		 * 清空文本框内容
		**/
		clear:function(id){
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			return $(id).val('');
		},

		/**
		 * 文件选择器
		**/
		text_button_file_select:function(id)
		{
			$.dialog.open(get_url("open","input","id="+id),{
				title: p_lang('附件管理器'),
				lock : true,
				width: "700px",
				height: "70%",
				resize: false
			});
		},

		/**
		 * 文件下载
		**/
		text_button_file_download:function(id)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			var file = $(id).val();
			if(!file){
				$.dialog.alert(p_lang('没有可下载的附件'));
				return false;
			}
			var url = get_url("res_action","download",'file='+$.str.encode(file));
			window.open(url);
		},

		/**
		 * 图片选择器
		**/
		text_button_image_select:function(id)
		{
			$.dialog.open(get_url("open","input","id="+id+"&type=image"),{
				title: p_lang('图片管理器'),
				lock : true,
				width: "700px",
				height: "70%",
				resize: false
			});
		},

		/**
		 * 图片预览
		**/
		text_button_image_preview:function(id)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			var file = $(id).val();
			if(!file){
				$.dialog.alert(p_lang('没有指定图片'));
				return false;
			}
			var url = get_url("res_action","view",'file='+$.str.encode(file));
			$.dialog.open(url,{
				title: p_lang('预览图片'),
				lock: true,
				width: '700px',
				height: '70%',
				resize: false,
				ok: true
			});
		},

		/**
		 * 视频选择器
		**/
		text_button_video_select:function(id)
		{
			var url = get_url("open","input","id="+id+"&type=video");
			$.dialog.open(url,{
				title: p_lang('视频管理器'),
				lock : true,
				width: "700px",
				height: "70%"
			});
		},

		/**
		 * 视频预览
		**/
		text_button_video_preview:function(id)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			var file = $(id).val();
			if(!file){
				$.dialog.alert(p_lang('没有指定视频'));
				return false;
			}
			var url = get_url("res_action","video","file="+$.str.encode(file));
			$.dialog.open(url,{
				title: p_lang('视频预览'),
				lock: true,
				width: '670px',
				height: '510px',
				ok:true
			});
		},

		/**
		 * 网址选择器
		**/
		text_button_url_select:function(id)
		{
			var url = get_url("open","url","id="+id);
			$.dialog.open(url,{
				title: p_lang('网址管理器'),
				lock : true,
				width: "700px",
				height: "70%"
			});
		},

		/**
		 * 网址预览
		**/
		text_button_url_open:function(id)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			var url = $(id).val();
			if(!url || url == "http://" || url == "https://"){
				$.dialog.alert(p_lang('未指定网址'));
				return false;
			}
			window.open(url);
		},

		/**
		 * 会员选择库
		**/
		text_button_user_select:function(id)
		{
			var url = get_url("open","user2","id="+id);
			$.dialog.open(url,{
				title: p_lang('会员列表'),
				lock : true,
				width: "700px",
				height: "70%",
				resize: false
			});
		},

		/**
		 * 快速插入文本
		**/
		text_button_quickwords:function(id,val,type)
		{
			if(id.substr(0,1) != '.' && id.substr(0,1) != '#'){
				id = '#'+id;
			}
			if(type && type == 'none'){
				$(id).val(val);
				return true;
			}
			var tmp = $(id).val();
			tmp = (tmp && tmp != 'undefined') ? (tmp+''+type+''+val) : val;
			$(id).val(tmp);
			return true;
		},

		/**
		 * 快速添加主题
		 * @参数 fid 字段ID
		 * @参数 input_id 表单字段名
		 * @参数 maxcount 最大数量，默认为1
		**/
		extitle_quickadd:function(fid,input_id,maxcount)
		{
			if(!maxcount || maxcount == 'undefined'){
				maxcount = 1;
			}
			var str = $("input[name="+input_id+"]").val();
			if(str && str != 'undefined'){
				var list = str.split(",");
				var total = list.length;
				if(total >= maxcount){
					$.dialog.alert(p_lang('超出系统限制添加的数量'));
					return false;
				}
			}
			var url = get_url('form','quickadd','id='+fid);
			$.dialog.open(url,{
				'title':p_lang('添加'),
				'width':'80%',
				'height':'70%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'lock':true,
				'okVal':p_lang('保存'),
				'cancel':true
			});
		},

		/**
		 * 快速编辑
		**/
		extitle_quickedit:function(id,fid)
		{
			var url = get_url('form','quickadd','id='+fid+"&tid="+id);
			$.dialog.open(url,{
				'title':p_lang('编辑 #'+id),
				'width':'80%',
				'height':'70%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'lock':true,
				'okVal':p_lang('保存'),
				'cancel':true
			});
		},

		extitle_list:function(fid,input_id,maxcount,ext)
		{
			if(!maxcount || maxcount == 'undefined'){
				maxcount = 1;
			}
			var str = $("input[name="+input_id+"]").val();
			if(str && str != 'undefined'){
				var list = str.split(",");
				var total = list.length;
				if(total >= maxcount){
					$.dialog.alert(p_lang('超出系统限制添加的数量'));
					return false;
				}
			}
			var url = get_url('form','quicklist','id='+fid+"&maxcount="+maxcount);
			if(ext && ext != 'undefined'){
				var list = ext.split(',');
				var is_ok = true;
				for(var i in list){
					var val = $("#"+list[i]).val();
					if(val == ''){
						is_ok = false;
						var title = $("#form_html_"+list[i]+" .title").html();
						title = title.replace(/<span.+>.+<\/span>/g,'');
						title = title.replace("：",'');
						$.dialog.alert('请先选择：'+title);
						break;
					}
					url += "&ext["+list[i]+"]="+$.str.encode(val);
				}
				if(!is_ok){
					return false;
				}
			}
			$.dialog.open(url,{
				'title':p_lang('选择'),
				'width':'90%',
				'height':'80%',
				'ok':true,
				'lock':true,
				'okVal':p_lang('关闭')
			});
		},

		/**
		 * 重载扩展字段
		 * @参数 id 模块字段ID
		 * @参数 identifier 标识
		**/
		extitle_reload:function(id,identifier)
		{
			var url = get_url('form','redata','id='+id);
			var content = $("#"+identifier).val();
			if(content){
				url += "&content="+$.str.encode(content);
			}
			$.phpok.json(url,function(data){
				if(data.status){
					if(data.info){
						$("#"+identifier+"_edit_preview").html(data.info);
					}
					return true;
				}
				return true;
			})
		},

		/**
		 * 删除已存在的主题，防止重复筛选
		**/
		extitle_remove_selected:function(identifier)
		{
			var opener = $.dialog.opener;
			var content = opener.$("#"+identifier).val();
			if(content){
				var list = content.split(",");
				for(var i in list){
					$("#title_"+list[i]).remove();
				}
			}
		},

		/**
		 * 选中已存在主题
		 * @参数 id 主题ID
		 * @参数 pid 项目ID
		 * @参数 identifier 要更新的标识内容
		**/
		extitle_select_action:function(id,pid,identifier,maxcount)
		{
			if(!maxcount || maxcount == 'undefined'){
				maxcount = 9999;
			}
			maxcount = parseInt(maxcount);
			var opener = $.dialog.opener;
			var content = opener.$("#"+identifier).val();
			if(content){
				var tmp = content.split(",");
				if(tmp.length >= maxcount){
					$.dialog.alert(p_lang('超出系统限制添加的数量'));
					return false;
				}
				content = content+","+id;
				var list = content.split(",");
				var rs = $.unique(list);
				content = rs.join(",");
				opener.$("#"+identifier).val(content);
			}else{
				opener.$("#"+identifier).val(id);
			}
			$("#title_"+id).remove();
			opener.$.phpokform.extitle_reload(pid,identifier);
			return true;
		},

		/**
		 * 向前移一位
		**/
		extitle_sortup:function(obj,id,identifier)
		{
			var t = [];
			var old = $(obj).parent().parent().attr("data-id");
			var total = $(obj).parent().parent().attr('data-total');
			var string = "td[data-name=taxis-"+identifier+"-"+id+"]";
			var chk = $(string).eq(0).attr('data-id');
			$(string).each(function(i){
				var id = $(this).attr("data-id");
				if($(string).eq(i+1).attr('data-id') == old){
					var val = $(string).eq(i+1).attr("data-value");
				}else{
					if(id == old){
						var val = $(string).eq(i-1).attr('data-value');
					}else{
						var val = $(this).attr("data-value");
					}
				}
				t.push({"id":id,"sort":val});
			});
			t = t.sort(function(a,b){return parseInt(a['sort'])>parseInt(b['sort']) ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['id'];
			}
			var data = list.join(",");
			$("#"+identifier).val(data);
			this.extitle_reload(id,identifier);
		},

		/**
		 * 向后移一位
		**/
		extitle_sortdown:function(obj,id,identifier)
		{
			var t = [];
			var old = $(obj).parent().parent().attr("data-id");
			var string = "td[data-name=taxis-"+identifier+"-"+id+"]";
			var chk = $(string).eq(0).attr('data-id');
			var num = 0;
			var old_value = 0;
			$(string).each(function(i){
				var id = $(this).attr("data-id");
				if(id == old){
					var val = $(string).eq(i+1).attr('data-value');
					num = i+1;
					old_value = $(this).attr('data-value');
				}else{
					if(id == $(string).eq(num).attr('data-id')){
						var val = old_value;
					}else{
						var val = $(this).attr('data-value');
					}
				}
				t.push({"id":id,"sort":val});
			});
			t = t.sort(function(a,b){return parseInt(a['sort'])>parseInt(b['sort']) ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['id'];
			}
			var data = list.join(",");
			$("#"+identifier).val(data);
			this.extitle_reload(id,identifier);
		},

		/**
		 * 删除操作
		**/
		extitle_delete:function(val,id,identifier)
		{
			var content = $("#"+identifier).val();
			if(!content || !val || val == '0' || content == '0' || val == 'undefined' || content == 'undefined'){
				return true;
			}
			if(content == val){
				$("#"+identifier).val('');
				this.extitle_reload(id,identifier);
				return true;
			}
			var list = content.split(',');
			var nlist = new Array();
			var m = 0;
			for(var i in list){
				if(list[i] != val){
					nlist[m] = list[i];
					m++;
				}
			}
			content = nlist.join(',');
			$("#"+identifier).val(content);
			var _self = this;
			$.phpok.json(get_url('form','quickdelete','fid='+id+"&id="+val),function(rs){
				if(rs.status){
					_self.extitle_reload(id,identifier);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	};
})(jQuery);


