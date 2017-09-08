/**
 * 会员自定义字段管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @日期 2017年03月31日
**/

function user_field_edit(id)
{
	var url = get_url("user","field_edit") + "&id="+id;
	$.dialog.open(url,{
		"title" : "编辑字段属性",
		"width" : "700px",
		"height" : "95%",
		"resize" : false,
		"lock" : true,
		'close'	: function(){
			direct(window.location.href);
		}
	});
}

//删除字段
function user_field_del(id,title)
{
	$.dialog.confirm(p_lang('确定要删除字段 {title} 吗？<br>删除后相应的字段内容也会被删除，不能恢复','<span class="red">'+title+'</span>'),function(){
		var url = get_url("user","field_delete") + "&id="+id;
		$.phpok.json(url,function(rs){
			if(rs.status){
				$.phpok.reload();
			}else{
				$.dialog.alert(rs.info);
			}
		})
	});
}

function user_field_quickadd(id)
{
	var url = get_url('user','fields_save','id='+id);
	$.phpok.json(url,function(rs){
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	})
}
