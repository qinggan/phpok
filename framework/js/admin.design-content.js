/**
 * 设计器管理中涉及到的JS
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月4日
**/
;(function($){
	$.admin_design_preg = {
		width:function(str)
		{
			var reg = /;width:([px\%\d]+);/;
			var reg_g = /;width:([px\%\d]+);/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		},
		height:function(str)
		{
			var reg = /;height:([px\%\d]+);/;
			var reg_g = /;height:([px\%\d]+);/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		},
		round:function(str)
		{
			var reg = /;border\-radius:([^;]+);/;
			var reg_g = /;border\-radius:([^;]+);/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		},
		link:function(str)
		{
			var reg = /href=\"([^\"]+)\"/;
			var reg_g = /href=\"([^\"]+)\"/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		},
		target:function(str)
		{
			var reg = /target=\"([^\"]+)\"/;
			var reg_g = /target=\"([^\"]+)\"/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		},
		alt:function(str)
		{
			var reg = /title=\"([^\"]+)\"/;
			var reg_g = /title=\"([^\"]+)\"/g;
			var result = str.match(reg_g);
			if(!result){
				return false;
			}
			var list = []
			for (var i = 0; i < result.length; i++) {
				var item = result[i]
				list.push(item.match(reg)[1])
			}
			return list[0];
		}
	}
})(jQuery);

function save()
{
	var id = $("#id").val();
	var opener = $.dialog.opener;
	var type = $("#type").val();
	if(type == 'editor'){
		var c = UE.getEditor('content').getContent();
		if(!c){
			$.dialog.alert('内容不能为空');
			return false;
		}
		opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(c);
		opener.$("div[pre-id="+id+"]").attr("pre-vtype","editor");
	}
	if(type == 'code'){
		var c = $("#content").val();
		if(!c){
			$.dialog.alert('内容不能为空');
			return false;
		}
		opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(c);
		opener.$("div[pre-id="+id+"]").attr("pre-vtype","code");
	}
	if(type == 'textarea'){
		var c = $("#content").val();
		if(!c){
			$.dialog.alert('内容不能为空');
			return false;
		}
		c = c.replace(/\n/g,'<br>');
		opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(c);
		opener.$("div[pre-id="+id+"]").attr("pre-vtype","textarea");
	}
	if(type == 'iframe'){
		var width = $("#width").val();
		var height = $("#height").val();
		var link = $("#link").val();
		if(!link || link == 'undefined'){
			$.dialog.alert('链接不能为空');
			return false;
		}
		var css = 'border:0;position:relative;';
		if(width && width != 'undefined'){
			if(width.indexOf('%') == -1 && width.indexOf('px') == -1){
				css += 'width:'+width+"px;";
			}else{
				css += 'width:'+width+";";
			}
		}
		if(height && height != 'undefined'){
			if(height.indexOf('%') == -1 && height.indexOf('px') == -1){
				css += 'height:'+height+"px;";
			}else{
				css += 'height:'+height+";";
			}
		}
		var html ='<div style="'+css+'">';
		html += '<div data-iframe="layer" style="background:none;z-index:2;position:absolute;left:0;top:0;width:100%;height:100%;"></div>';
		html += '<iframe src="'+link+'" style="width:100%;height:100%;overflow:hidden;z-index:1;" frameborder="0" scrolling="no"></iframe>';
		html += '</div>';
		opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(html);
		opener.$("div[pre-id="+id+"]").attr("pre-vtype","iframe");
		$.dialog.close();
		return true;
	}
	if(type == 'image'){
		var c = $("#content").val();
		if(!c){
			$.dialog.alert('内容不能为空');
			return false;
		}
		var gdtype = $("#gdtype").val();
		var round = $("#round").val();
		if(round && round != 'undefined'){
			if(round.indexOf('px') == -1 && round.indexOf('%') == -1){
				round += 'px';
			}
		}
		var width = $("#width").val();
		var height = $("#height").val();
		var link = $("#link").val();
		var target = $("#target").val();
		var alt = $("#alt").val();
		var url = get_url('res','info','id='+c);
		var css = 'border:0;';
		if(round && round != 'undefined'){
			css += 'border-radius:'+round+";";
		}
		if(width && width != 'undefined'){
			if(width.indexOf('%') == -1 && width.indexOf('px') == -1){
				css += 'width:'+width+"px;";
			}else{
				css += 'width:'+width+";";
			}
		}
		if(height && height != 'undefined'){
			if(height.indexOf('%') == -1 && height.indexOf('px') == -1){
				css += 'height:'+height+"px;";
			}else{
				css += 'height:'+height+";";
			}
		}
		$.phpok.json(url,function(rs){
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			if(!alt || alt == 'undefined'){
				alt = rs.info.title;
			}
			var html = '';
			if(link && link != 'undefined'){
				html += '<a href="'+link+'"';
				if(alt){
					html += ' title="'+alt+'"';
				}
				if(target){
					html += ' target="'+target+'"';
				}
				html += '>';
			}
			var img = rs.info.filename;
			if(gdtype && rs.info.gd && rs.info.gd[gdtype]){
				img = rs.info.gd[gdtype];
			}
			
			html += '<img src="'+img+'" style="'+css+'" alt="'+alt+'" />';
			if(link && link != 'undefined'){
				html += '</a>';
			}
			opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(html);
			opener.$("div[pre-id="+id+"]").attr("pre-vtype","image").attr("pre-gdtype",gdtype).attr("pre-image",c);
			$.dialog.close();
			return true;
		});
		return false;
	}
	if(type == 'video'){
		var link = $("#link").val();
		if(!link){
			$.dialog.alert('视频不能为空');
			return false;
		}
		var html = '<video src="'+link+'"';
		if($('#autoplay').prop('checked')){
			html += ' autoplay';
		}
		if($('#loop').prop('checked')){
			html += ' loop';
		}
		if($('#muted').prop('checked')){
			html += ' muted';
		}
		if($('#controls').prop('checked')){
			html += ' controls';
		}
		if($('#playsinline').prop('checked')){
			html += ' playsinline';
		}
		var preload = $("input[name=preload]:checked").val();
		html += ' preload="'+preload+'"';
		var poster = $("#poster").val();
		if(poster){
			html += ' poster="'+poster+'"';
		}
		var width = $("#width").val();
		if(width){
			html += ' width="'+width+'"';
		}
		var height = $("#height").val();
		if(height){
			html += ' height="'+height+'"';
		}
		html += '></video>';
		opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html(html);
		opener.$("div[pre-id="+id+"]").attr("pre-vtype","video");
	}
	if(type == 'calldata'){
		var obj = opener.$("div[pre-id="+id+"]");
		obj.attr("pre-vtype","calldata");
		var code = $("#code").val();
		if(!code){
			$.dialog.alert('请选择一个调用接口');
			return false;
		}
		var param = $("#param").val();
		if(param){
			param = param.replace(/>/g,'&gt;');
			param = param.replace(/</g,'&lt;');
			param = param.replace(/"/g,'&quot;');
			param = param.replace(/'/g,'&apos;');
		}
		var tplfile = $("#tplfile").val();
		if(!tplfile){
			$.dialog.alert('模板不能为空');
			return false;
		}
		obj.attr('pre-code',code);
		obj.attr("pre-param",param);
		obj.attr("pre-tplfile",tplfile);
		var iframe_url = api_url('call','admin_preview','id='+id+"&code="+$.str.encode(code));
		if(param){
			iframe_url += "&param="+$.str.encode(param);
		}
		iframe_url += "&tplfile="+$.str.encode(tplfile);
		
		var html = '<!-- content-'+id+' --><div style="background:none;z-index:2;position:absolute;left:0;top:0;width:100%;height:100%;"></div>';
		html += '<iframe src="'+iframe_url+'" style="border:0;margin:0;padding:0;background-color:transparent;z-index:1"';
		html += 'id="iframe_'+id+'" name="iframe_'+id+'" scrolling="0" width="100%" allowtransparency="true"';
		html += ' frameborder="0" marginheight="0" marginwidth="0" ></iframe><!-- /content-'+id+' -->';
		obj.find('div[pre-type=content]').html(html);//加标识，同时嵌入 iframe 方便预览
	}
	$.dialog.close();
	return true;
}

function update_change_url(type)
{
	var id = $("#id").val();
	var url = get_url('design','content','id='+id+"&type="+type);
	$.phpok.go(url);
}

function design_update_iframe()
{
	var id = $("#id").val();
	var opener = $.dialog.opener;
	var obj = opener.$("div[pre-id="+id+"]").find("div[pre-type=content]");
	var src = obj.find("iframe").attr("src");
	if(src && src != 'undefined'){
		$("#link").val(src);
	}
	var c = obj.html();
	var width = $.admin_design_preg.width(c);
	if(width && width != 'undefined'){
		$("#width").val(width);
	}
	var height = $.admin_design_preg.height(c);
	if(height && height != 'undefined'){
		$("#height").val(height);
	}
}

function design_update_image()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var c = opener.$("div[pre-id="+id+"]").find("div[pre-type=content]").html();
	if(!c || c == 'undefined'){
		return true;
	}
	var gdtype = opener.$("div[pre-id="+id+"]").attr("pre-gdtype");
	if(gdtype){
		$("#gdtype").val(gdtype);
	}
	var res_id = opener.$("div[pre-id="+id+"]").attr("pre-image");
	if(!res_id || res_id == 'undefined'){
		return false;
	}
	var width = $.admin_design_preg.width(c);
	if(width){
		$("#width").val(width);
	}
	var height = $.admin_design_preg.height(c);
	if(height){
		$("#height").val(height);
	}
	var round = $.admin_design_preg.round(c);
	if(round){
		$("#round").val(round);
	}
	var link = $.admin_design_preg.link(c);
	if(link){
		$("#link").val(link);
	}
	var alt = $.admin_design_preg.alt(c);
	if(alt){
		$("#alt").val(alt);
	}
	var target = $.admin_design_preg.target(c);
	if(target){
		$("#target").val(target);
	}
}

function update_change_cate(id,cateid)
{
	var url = get_url('call','cate_list',"id="+id);
	//异步更新分类
	$.phpok.json(url,function(data){
		if(!data.status){
			$("#catelist_html").html('').hide();
			return true;
		}
		var cate = data.info.cate;
		var rslist = data.info.catelist;
		var html = '<select name="cateid" id="cateid">';
		var space = '';
		if(cate){
			html += '<option value="'+cate.id+'">'+p_lang('根分类')+cate.title+'</option>';
			space = '&nbsp; &nbsp;';
		}else{
			html += '<option value="">'+p_lang('请选择…')+'</option>';
		}
		if(rslist){
			for(var i in rslist){
				html += '<option value="'+rslist[i].id+'"';
				if(rslist[i].id == cateid){
					html += ' selected';
				}
				html += '>'+space+' '+rslist[i]._space +  ' '+rslist[i].title+'</option>';
			}
		}
		html += '</select>';
		$("#catelist_html").html(html).show();
		layui.form.render();
	});
	return false;
}

function design_update_video()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var obj = opener.$("div[pre-id="+id+"]").find("video");
	if(!obj || obj.length<1){
		return false
	}
	var video_obj = obj[0];
	var link = obj.attr("src");
	$("#link").val(link);
	var controls = obj.attr("controls");
	if(controls && controls != 'undefined'){
		$("#controls").prop("checked",true);
	}
	if(video_obj.loop){
		$("#loop").prop("checked",true);
	}
	if(video_obj.autoplay){
		$("#autoplay").prop("checked",true);
	}
	if(video_obj.muted){
		$("#muted").prop("checked",true);
	}
	if(video_obj && video_obj.attributes && video_obj.attributes.playsinline){
		$("#playsinline").prop("checked",true);
	}
	var preload = obj.attr("preload");
	if(!preload || preload == 'undefined'){
		preload = 'metadata';
	}
	$("input[name=preload][value="+preload+"]").click(); //执行选中点击
	var poster = obj.attr("poster");
	if(poster && poster != 'undefined'){
		$("#poster").val(poster);
	}
	var width = obj.attr("width");
	if(!width || width == 'undefined'){
		width = obj.css("width");
	}
	if(width && width != 'undefined'){
		$("#width").val(width);
	}
	var height = obj.attr("height");
	if(!height || height == 'undefined'){
		height = obj.css("height");
	}
	if(height && height != 'undefined'){
		$("#height").val(height);
	}
	return true;
}

function design_update_calldata()
{
	var opener = $.dialog.opener;
	var id = $("#id").val();
	var obj = opener.$("div[pre-id="+id+"]");
	var code = obj.attr("pre-code");
	if(code){
		$("#code").val(code);
	}
	var param = obj.attr("pre-param");
	if(param){
		param = param.replace(/&gt;/g,'>');
		param = param.replace(/&lt;/g,'<');
		param = param.replace(/&quot;/g,'"');
		param = param.replace(/&apos;/g,"'");
		$("#param").val(param);
	}
	var tplfile = obj.attr('pre-tplfile');
	if(tplfile){
		$("#tplfile").val(tplfile);
		update_show_preview(tplfile);
	}
}

function update_show_preview(tplfile)
{
	$("#note").html('');
	var url = get_url('design','tplfile','filename='+$.str.encode(tplfile));
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		var info = rs.info;
		if(info.img){
			$("#preview").html('<img src="'+info.img+'" style="max-width:100%;max-height:100%;"/>');
		}else{
			$("#preview").html('<div style="padding:10px">暂无预览</div>');
		}
		if(info.note){
			$("#note").html(info.note);
		}
		return true;
	})
}


$(document).ready(function(){
	layui.form.on('select(type)', function(data){
		update_change_url(data.value);
	});
	layui.form.on('select(tplfile)', function(data){
		update_show_preview(data.value);
	});
});