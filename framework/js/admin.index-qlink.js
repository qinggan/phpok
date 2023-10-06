/**
 * 快捷链接涉及到的JS操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年2月14日
**/
function ctrl_to_func(val)
{
	if(val == '' || !val || val == 'undefined'){
		$("#func_file").html('<option value="">'+p_lang('请选择方法…')+'</option>');
		$("#func_file_html").hide();
		return true;
	}
	var url = get_url('index','funclist','id='+val);
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			$("#func_file").html('<option value="">'+p_lang('请选择方法…')+'</option>');
			$("#func_file_html").hide();
			return true;
		}
		var list = rs.info;
		var html = '<option value="">'+p_lang('请选择方法…')+'</option>';
		for(var i in list){
			html += '<option value="'+list[i].id+'">'+list[i].id;
			if(list[i].id != list[i].title){
				html += '_'+$.trim(list[i].title);
			}
			html += '</option>';
		}
		$("#func_file").html(html);
		$("#func_file_html").show();
		ctrl_func_create();
		return true;
	})
}

function ctrl_func_create()
{
	var ctrl = $("#ctrl_file").val();
	if(!ctrl || ctrl == 'undefined'){
		$.dialog.alert(p_lang('未指定控制器'));
	}
	var func = $("#func_file").val();
	var url = adminfile+"?"+ctrl_id+"="+ctrl;
	if(func && func != 'undefined' && func != 'index'){
		url += "&"+func_id+"="+func;
	}
	$("#link").val(url);
}

function set_icon(input)
{
	var url = get_url('system','icon_text','input='+input);
	var icon = $("#menu_ico").val();
	if(icon && icon != 'undefined'){
		url += "&icon="+icon;
	}
	$.dialog.open(url,{
		'title':p_lang('设置图标'),
		'width':'70%',
		'height':'70%',
		'lock':true,
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert('iframe还没加载完毕呢');
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':p_lang('提交保存'),
		'cancel':true
	});
}

function icon_preview(info)
{
	$("#menu_ico").val(info);
	$("#menu_ico_preview").removeClass().addClass('icon-'+info).show();
}

function icon_clear()
{
	$("#menu_ico").val('');
	$("#menu_ico_preview").removeClass();
}

function save(obj)
{
	$(obj).ajaxSubmit({
		'url':get_url('index','qlink_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.tips('操作成功，请关闭页面',function(){
					$.admin.close();
				});
				return true;
			}
			$.dialog.tips(rs.info);
			return false;
		}
	});
}