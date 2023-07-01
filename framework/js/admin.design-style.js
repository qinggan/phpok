/**
 * 样式设计中涉及到的JS操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2021年1月5日
**/

function save()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var style = {};
	var obj = opener.$("div[pre-id="+id+"]").find("div[pre-type=content]");
	var align = $("input[name=text-align]:checked").val();
	if(align && align != 'undefined'){
		obj.css("text-align",align);
	}else{
		obj.css("text-align",'');
	}
	//判断ext_class
	var old = obj.attr("pre-class");
	if(old){
		var tmp = old.split(" ");
		for(var i in tmp){
			var t = $.trim(tmp[i]);
			if(t && t != 'undefined'){
				obj.removeClass(t);
			}
		}
	}
	var ext_class = $("#ext-class").val();
	if(ext_class){
		var tmp = ext_class.split(" ");
		for(var i in tmp){
			var t = $.trim(tmp[i]);
			if(t && t != 'undefined'){
				obj.addClass(t);
			}
		}
		obj.attr("pre-class",ext_class);
	}else{
		obj.attr("pre-class",'');
	}
	//清除旧版样式
	var old = obj.attr("pre-style");
	if(old){
		var tmp = old.split(";");
		for(var i in tmp){
			var t = $.trim(tmp[i]);
			if(t && t != 'undefined'){
				var tt = t.split(":");
				obj.css(tt[0],'');
			}
		}
	}
	var ext_style = $("#ext-css").val();
	if(ext_style){
		var tmp = ext_style.split(";");
		for(var i in tmp){
			var t = $.trim(tmp[i]);
			if(t && t != 'undefined'){
				var tt = t.split(":");
				obj.css(tt[0],tt[1]);
			}
		}
		obj.attr("pre-style",ext_style);
	}else{
		obj.attr("pre-style",'');
	}
	var bgcolor = $("#bgcolor").val();
	if(bgcolor && bgcolor != 'undefined'){
		obj.css("background-color",bgcolor);
	}else{
		obj.css("background-color",'');
	}
	var bgimg = $("#background-image").val();
	if(bgimg && bgimg != 'undefined'){
		obj.css("background-image","url("+bgimg+")");
		obj.css("background-size","cover");
	}else{
		obj.css("background-image",'');
	}
	var bg_position = $("input[name=bg_position]:checked").val();
	if(bg_position && bg_position != 'undefined'){
		obj.css("background-position",bg_position);
	}else{
		obj.css("background-position",'');
	}
	obj.attr("data-wow-duration",$("#data-wow-duration").val());
	obj.attr("data-wow-delay",$("#data-wow-delay").val());
	obj.attr("data-wow-offset",$("#data-wow-offset").val());
	obj.attr("data-wow-iteration",$("#data-wow-iteration").val());
	var style_in = $("#style_in").val();
	if(style_in && style_in != '0' && style_in != 'undefined'){
		obj.attr("wow-in",style_in);
		obj.attr('wow-action','true');
	}else{
		obj.attr("wow-in",'');
	}
	var style_out = $("#style_out").val();
	if(style_out && style_out != '0' && style_out != 'undefined'){
		obj.attr("wow-out",style_out);
		obj.attr('wow-action','true');
	}else{
		obj.attr("wow-out",'');
	}
	$.dialog.close();
	return false;
}

function design_update_col_style()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var obj = opener.$("div[pre-id="+id+"]").find("div[pre-type=content]");
	var align = obj.css("text-align");
	if(align && align != "undefined"){
		$("input[name=text-align][value="+align+"]").click();
	}
	var wow_in = obj.attr("wow-in");
	if(wow_in){
		$("#style_in").val(wow_in);
	}
	var wow_out = obj.attr("wow-out");
	if(wow_out){
		$("#style_out").val(wow_out);
	}
	$("#data-wow-duration").val(obj.attr("data-wow-duration"));
	$("#data-wow-delay").val(obj.attr("data-wow-delay"));
	$("#data-wow-offset").val(obj.attr("data-wow-offset"));
	$("#data-wow-iteration").val(obj.attr("data-wow-iteration"));

	var bgcolor = obj.css("background-color");
	if(bgcolor && bgcolor != 'undefined' && bgcolor != 'rgba(0, 0, 0, 0)'){
		$("#bgcolor").val(bgcolor);
	}
	var position = $.admin_design.bg_position(obj);
	var chk = /[a-zA-Z\s]$/;
	if(position && position != 'undefined' && chk.test(position)){
		$("input[name=bg_position][value='"+position+"']").click();
	}
	var bgimg = obj.css("background-image");
	if(bgimg && bgimg != 'undefined'){
		bgimg = bgimg.replace(/url\((.+)\)/g,"$1");
		bgimg = bgimg.replace(/\"/g,'');
		bgimg = bgimg.replace(/\'/g,'');
		bgimg = bgimg.replace(webroot,'');
		if(bgimg != 'none'){
			$("#background-image").val(bgimg);
		}
	}
	var ext_class = obj.attr("pre-class");
	if(ext_class){
		$("#ext-class").val(ext_class);
	}
	var ext_style = obj.attr("pre-style");
	if(ext_style){
		$("#ext-css").val(ext_style);
	}
}

$(document).ready(function(){
	design_update_col_style();
});