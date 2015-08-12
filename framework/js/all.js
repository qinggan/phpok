/***********************************************************
	Filename: {phpok}/js/all.js
	Note	: 全局模块参数设置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-19 20:20
***********************************************************/
//检查网站配置域名是否符合要求
function all_setting_check()
{
	var title = $("#title").val();
	if(!title){
		$.dialog.alert(p_lang('网站名称不能为空'));
		return false;
	}
	//检测域名
	var domain_check = _domain_check($("#domain").val());
	if(!domain_check){
		return false;
	}
	return true;
}

function _domain_check(domain,isadd,domain_id)
{
	if(!domain){
		$.dialog.alert(p_lang('域名不能为空'));
		return false;
	}
	domain = domain.toLowerCase();
	if(domain.substr(0,7) == "http://" || domain.substr(0,8) == "https://"){
		$.dialog.alert(p_lang('域名不能以http://或https://开头'));
		return false;
	}
	var chk = new RegExp('/');
	if(chk.test(domain)){
		$.dialog.alert(p_lang('域名填写不正确'));
		return false;
	}
	//检测此域名是否被使用
	var url = get_url("all","domain_check") + "&domain="+$.str.encode(domain);
	if(isadd && isadd != "undefined"){
		url += "&isadd=1";
	}
	if(domain_id && domain_id != "undefined"){
		url += "&id="+domain_id;
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

//添加域名
function domain_add()
{
	var domain = $("#domain_0").val();
	var domain_check = _domain_check(domain,1);
	if(!domain_check){
		return false;
	}
	var url = get_url("all","domain_save")+"&domain="+$.str.encode(domain);
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.dialog.alert(p_lang('域名添加成功'),function(){
			$.phpok.reload();
		},'succeed');
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

//更新域名
function domain_update(id)
{
	var domain = $("#domain_"+id).val();
	var domain_check = _domain_check(domain,0,id);
	if(!domain_check){
		return false;
	}
	var url = get_url("all","domain_save")+"&domain="+$.str.encode(domain);
	url += "&id="+id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.dialog.alert(p_lang('域名更新成功'),function(){
			$.phpok.reload();
		},'succeed');
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

//设置主域名
function domain_default(id)
{
	var url = get_url("all","domain_default")+"&id="+id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

//删除域名
function domain_delete(id)
{
	$.dialog.confirm(p_lang('确定要删除此域名吗'),function(){
		var url = get_url("all","domain_delete")+"&id="+id;
		var rs = $.phpok.json(url);
		if(rs.status == "ok"){
			$.phpok.reload();
		}else{
			alert(rs.content);
			return false;
		}
	});
}

//添加或修改扩展组ID
function g_ext_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(!title){
		$.dialog.alert(p_lang('名称不能为空'));
		return false;
	}
	var identifier = $("#identifier").val();
	if(!identifier){
		$.dialog.alert(p_lang('标识串不能为空'));
		return false;
	}
	var chk = $.str.identifier(identifier);
	if(!chk){
		$.dialog.alert(p_lang('标识串不符合条件要求'));
		return false;
	}
	//检测是否被使用了
	identifier = identifier.toLowerCase();
	if(identifier == "config" || identifier == "phpok"){
		$.dialog.alert(p_lang('标识串不符合条件要求'));
		return false;
	}
	var url = get_url("all","all_check")+"&identifier="+$.str.encode(identifier);
	if(id){
		url +="&id="+id;
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		$.dialog.alert(rs.content);
		return false;
	}
	var ico = $("input[name=ico]").val();
	if(!ico){
		$.dialog.alert(p_lang('请选择一个图标'));
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
		$.dialog.alert(p_lang('添加异常，未指定ID'));
		return false;
	}
	url += '&all_id='+all_id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		autosave("ext_post","all",auto_refresh);
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

//删除扩展字段
function all_ext_delete(id,title)
{
	var cate_id = $("#id").val();
	url = get_url("all","ext_delete");
	if(!cate_id){
		$.dialog.alert(p_lang('未指定全局ID'));
		return false;
	}
	url += "&all_id="+cate_id;
	url += "&id="+id;
	$.dialog.confirm(p_lang('确定要删除这个扩展字段吗？'),function(){
		var rs = $.phpok.json(url);
		if(rs.status == 'ok'){
			autosave("ext_post","all",auto_refresh);
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function ext_g_delete(id)
{
	$.dialog.confirm(p_lang('确定要删除此组信息吗？删除后相关数据都会一起被删除'),function(){
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

function set_mobile(id)
{
	var url = get_url('all','domain_mobile','act_mobile=1&id='+id);
	var rs = $.phpok.json(url);
	if(rs.status == 'ok'){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}

function unset_mobile(id)
{
	var url = get_url('all','domain_mobile','act_mobile=0&id='+id);
	var rs = $.phpok.json(url);
	if(rs.status == 'ok'){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}