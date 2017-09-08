/**
 * 后台模块管理涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2016年07月19日
**/

//删除字段
function module_field_del(id,title)
{
	$.dialog.confirm('确定要删除字段：<span class="red">'+title+'</span>？<br />删除此字段将同时删除相应的内容信息',function(){
		var url = get_url("module","field_delete") + "&id="+id;
		var rs = $.phpok.json(url);
		if(rs.status == "ok"){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function module_field_add(id,fid)
{
	var url = get_url("module","field_add") + "&id="+id;
	url += "&fid="+fid;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
	}
}

function module_export(id)
{
	var url = get_url('module','export','id='+id);
	$.phpok.go(url);
}

//删除模块信息
function module_del(id,title)
{
	$.dialog.confirm("确定要删除模块：<span style='color:red;font-weight:bold;'>"+title+"</span>?<br />如果模块中有内容，也会相应的被删除，请慎用！",function(){
		var url = get_url("module","delete")+"&id="+id;
		var rs = json_ajax(url);
		if(rs && rs.status == 'ok'){
			$.phpok.reload();
		}else{
			if(!rs.content){
				rs.content = "删除失败";
			}
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

//设置状态
function set_status(id)
{
	var url = get_url("module","status") + '&id='+id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		if(!rs.content) rs.content = '0';
		var oldvalue = $("#status_"+id).attr("value");
		var old_cls = "status"+oldvalue;
		$("#status_"+id).removeClass(old_cls).addClass("status"+rs.content);
		$("#status_"+id).attr("value",rs.content);
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

//编辑字段
function module_field_edit(id)
{
	var url = get_url("module","field_edit") + "&id="+id;
	$.dialog.open(url,{
		'title':'编辑字段 #'+id,
		'lock':true,
		'width':'600px',
		'height':'70%',
		'resize':false,
		'drag':false,
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':'保存编辑信息',
		'cancel':function(){
			return true;
		}
	})
}

function module_field_create(id)
{
	var url = get_url("module","field_create") + "&mid="+id;
	$.dialog.open(url,{
		'title':'添加字段',
		'lock':true,
		'width':'650px',
		'height':'70%',
		'resize':false,
		'drag':false,
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':'提交保存',
		'cancel':function(){
			return true;
		}
	})
}

function module_layout(id,title)
{
	var url = get_url("module","layout") + "&id="+id;
	url = $.phpok.nocache(url);
	$.dialog.open(url,{
		"title":"模型："+title+" 后台列表布局",
		"width":"700px",
		"height":"400px",
		"win_min":false,
		"win_max":false,
		"resize": false,
		"lock": true
	});
}

function module_copy(id,title)
{
	var url = get_url("module","copy")+"&id="+id;
	url = $.phpok.nocache(url);
	$.dialog.prompt("请设置新模块的名称：",function(val){
		if(!val)
		{
			alert("名称不能为空");
			return false;
		}
		url += "&title="+$.str.encode(val);
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("模型 <span class='red'>"+val+"</span> 创建成功",function(){
				$.phpok.reload();
			});
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

/**
 * 模块导入
**/
function module_import()
{
	var url = get_url('module','import');
	$.dialog.open(url,{
		'title':p_lang('模块导入'),
		'lock':true,
		'width':'500px',
		'height':'150px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe还没加载完毕呢'));
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':p_lang('导入模块'),
		'cancelVal':p_lang('取消'),
		'cancel':function(){return true;}
	});
}

/**
 * 模块创建
**/
function module_create()
{
	var url = get_url('module','set');
	$.dialog.open(url,{
		'title':p_lang('模块添加'),
		'lock':true,
		'width':'650px',
		'height':'400px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe还没加载完毕呢'));
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':p_lang('保存'),
		'cancelVal':p_lang('取消'),
		'cancel':function(){return true;}
	});
}

/**
 * 模块编辑
**/
function module_set(id)
{
	var url = get_url('module','set','id='+id);
	$.dialog.open(url,{
		'title':p_lang('模块修改')+" #"+id,
		'lock':true,
		'width':'650px',
		'height':'400px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe还没加载完毕呢'));
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':p_lang('保存'),
		'cancelVal':p_lang('取消'),
		'cancel':function(){return true;}
	});
}