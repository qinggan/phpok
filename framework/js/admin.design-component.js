/**
 * 设计器管理中涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月12日
**/

function save()
{
	var id = $("#id").val();
	var opener = $.dialog.opener;
	var $obj = $("input[name=code]:checked");
	var type = $obj.attr('data-vtype');
	if(!type){
		$.dialog.tips('未指定组件');
		return false;
	}
	var code = $obj.val();
	var obj = opener.$("div[pre-id="+id+"]");
	obj.attr("pre-vtype",type);
	if(type == 'iframe'){
		var css = 'border:0;position:relative;';
		var width = $obj.attr("data-ext-width");
		var height = $obj.attr("data-ext-height");
		if(width && width != 'undefined'){
			css += 'width:'+width+';';
		}
		if(width && width != 'undefined'){
			css += 'width:'+height+';';
		}
		var html ='<div style="'+css+'">';
		html += '<div data-iframe="layer" style="background:none;z-index:2;position:absolute;left:0;top:0;width:100%;height:100%;"></div>';
		html += '<iframe src="about:blank" style="width:100%;height:100%;overflow:hidden;z-index:1;" frameborder="0" scrolling="no"></iframe>';
		html += '</div>';
		obj.find("div[pre-type=content]").html(html);
		return true;
	}
	if(type == 'calldata'){
		var calldata = $obj.attr("data-ext-calldata");
		obj.attr("pre-code",code);
		obj.attr("data-ext-calldata",calldata);
		var iframe_url = api_url('call','admin_preview','id='+id+"&code="+code);
		if(calldata){
			iframe_url+="&calldata="+calldata;
		}
		var html = '<!-- content-'+id+' --><div style="background:none;z-index:2;position:absolute;left:0;top:0;width:100%;height:100%;"></div>';
		html += '<iframe src="'+iframe_url+'" style="border:0;margin:0;padding:0;background-color:transparent;z-index:1"';
		html += 'id="iframe_'+id+'" name="iframe_'+id+'" scrolling="0" width="100%" allowtransparency="true"';
		html += ' frameborder="0" marginheight="0" marginwidth="0" ></iframe><!-- /content-'+id+' -->';
		obj.find('div[pre-type=content]').html(html);//加标识，同时嵌入 iframe 方便预览
	}
	if(type == 'image'){
		var c = $obj.attr("data-ext-res_id");
		var width = $obj.attr("data-ext-width");
		var height = $obj.attr("data-ext-height");
		var css = 'border:0;';
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
		if(c){
			var url = get_url('res','info','id='+c);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				var html = '';
				var img = rs.info.filename;
				html += '<img src="'+img+'" style="'+css+'" alt="'+rs.info.title+'" title="'+rs.info.title+'" />';
				obj.find("div[pre-type=content]").html(html);
				obj.attr("pre-vtype","image").attr("pre-image",c);
				$.dialog.close();
				return true;
			});
			return false;
		}
		var html = '<img src="images/picture_default.png" style="'+css+'" />';
		obj.find("div[pre-type=content]").html(html);
		obj.attr("pre-vtype","image").attr("pre-image",0);
		$.dialog.close();
		return false;
	}
	return true;
}