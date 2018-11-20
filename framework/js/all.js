/***********************************************************
	Filename: {phpok}/js/all.js
	Note	: 全局模块参数设置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-19 20:20
***********************************************************/

//添加或修改扩展组ID
function g_ext_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(!title){
		layer.alert(p_lang('名称不能为空'));
		return false;
	}
	var identifier = $("#identifier").val();
	if(!identifier){
		layer.alert(p_lang('标识串不能为空'));
		return false;
	}
	var chk = $.str.identifier(identifier);
	if(!chk){
		layer.alert(p_lang('标识串不符合条件要求'));
		return false;
	}
	//检测是否被使用了
	identifier = identifier.toLowerCase();
	if(identifier == "config" || identifier == "phpok"){
		layer.alert(p_lang('标识串不符合条件要求'));
		return false;
	}
	var url = get_url("all","all_check")+"&identifier="+$.str.encode(identifier);
	if(id){
		url +="&id="+id;
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		layer.alert(rs.content);
		return false;
	}
	var ico = $("input[name=ico]").val();
	if(!ico){
		layer.alert(p_lang('请选择一个图标'));
		return false;
	}
	return true;
}

//添加字段
function all_add_ext(id,t)
{
	var url = get_url("all","ext_add") + "&id="+id;
	var all_id = $("#id").val();
	if(!all_id){
		layer.alert(p_lang('添加异常，未指定ID'));
		return false;
	}
	url += '&all_id='+all_id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		autosave("ext_post","all",auto_refresh);
	}else{
		layer.alert(rs.content);
		return false;
	}
}

//删除扩展字段
function all_ext_delete(id,title)
{
	var cate_id = $("#id").val();
	url = get_url("all","ext_delete");
	if(!cate_id){
		layer.alert(p_lang('未指定全局ID'));
		return false;
	}
	url += "&all_id="+cate_id;
	url += "&id="+id;
	layer.confirm(p_lang('确定要删除这个扩展字段吗？'),function(){
		var rs = $.phpok.json(url);
		if(rs.status == 'ok'){
			autosave("ext_post","all",auto_refresh);
		}else{
			layer.alert(rs.content);
			return false;
		}
	});
}

function ext_g_delete(id)
{
	layer.confirm(p_lang('确定要删除此组信息吗？删除后相关数据都会一起被删除'),function(){
		$.phpok.go(get_url('all','ext_gdelete','id='+id));
	});
}


function email_setting(val)
{
	if(val == 1){
		$("#email_setting").show();
	}else{
		$("#email_setting").hide();
	}
}

function set_url_type(val)
{
	if(!val || val == 'undefined'){
		val = 'default';
	}
	$("#url_type_default,#url_type_rewrite,#url_type_html").hide();
	$("#url_type_"+val).show();
}
