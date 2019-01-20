/**
 * 图标上传动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月13日
**/
var obj_upload = {};
$(document).ready(function(){
	obj_upload = new $.admin_upload({
		"multiple"	: false,
		"id" : "upload",
		'pick':{'id':'#upload_picker','multiple':false},
		'resize':false,
		"server": get_url('upload','img'),
		"filetypes" : "jpg,gif,png,jpeg",
		'accept' : {'title':p_lang('图片'),'extensions':'jpg,gif,png,jpeg','mimeTypes': 'image/*'},
		"formData" :{session_name:session_id,'folder':'res/ico/'},
		'fileVal':'upfile',
		'sendAsBinary':true,
		'auto':true,
		"success":function(file,data){
			if(!data.status || data.status != 'ok'){
				$.dialog.alert(data.info);
				return false;
			}
			var info = data.info ? data.info : data.content;
			var html = '<li class="mbtm"><input type="radio" name="ico" value="'+info+'" checked /> <img src="'+info+'" width="48px" height="48px" /></li>';
			$("#phpok_upload").before(html);
			return true;
		}
	});
	obj_upload.uploader.on('uploadFinished',function(){
		return true;
	});
});
function save()
{
	var opener = $.dialog.opener;
	var info = $("input[name=ico]:checked").val();
	if(!info){
		$.dialog.alert('请选择图标');
		return false;
	}
	opener.$("#ico").val(info);
	$.dialog.close();
	return true;
}

function cancel()
{
	return obj_upload.uploader.stop();
}