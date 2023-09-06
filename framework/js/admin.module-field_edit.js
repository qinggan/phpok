/**
 * 字段编辑涉及到的JS事件
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License
 * @时间 2023年9月6日
 * @更新 2023年9月6日
**/

function save(obj,id)
{
	$(obj).ajaxSubmit({
		'url':get_url('module','field_edit_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.tips(p_lang('字段编辑成功'));
				$.admin.close(get_url('module','fields','id='+id));
				return false;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
	return false;
}

$(document).ready(function(){
	layui.use('form',function(){
		var form = layui.form;
		form.on('select(form_type)',function(data){
			var id = $(data.elem).attr("data-id");
			$._configForm.option(data.value,'form_type_ext',id,'module');
		});
	})
});