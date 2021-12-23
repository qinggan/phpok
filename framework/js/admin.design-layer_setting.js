/**
 * 设计器外框属性设置
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月12日
**/

function save()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var obj = opener.$("div[pre-id="+id+"]");
	var width = $("input[name=width]:checked").val();
	obj.attr("pre-width",width);
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
	var mheight = $("#min-height").val();
	if(mheight && mheight != 'undefined'){
		obj.css("min-height",mheight+"px");
	}else{
		obj.css("min-height",'');
	}
	$.dialog.close();
	return false;
}

function design_update_col_style2()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var obj = opener.$("div[pre-id="+id+"]");
	var p_width = obj.attr('pre-width');
	if(p_width && p_width != 'undefined'){
		$("input[name=width][value="+p_width+"]").click();
	}
	var m_height = $.admin_design.mheight(obj);
	if(m_height && m_height != 'undefined'){
		$("#min-height").val(m_height);
	}

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
	design_update_col_style2();
});