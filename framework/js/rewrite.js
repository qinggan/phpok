/**************************************************************************************************
	文件： {phpok}/js/rewrite.js
	说明： 伪静态页网址
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2016年02月14日
***************************************************************************************************/
function insert_input(val,id,space)
{
	if(!id || id == 'undefined'){
		id = 'rule';
	}
	if(!space || space == 'undefined'){
		space = '';
	}
	var info = $("#"+id).val();
	if(info){
		val = info + space +val;
	}
	$("#"+id).val(val);
}

function update2(val,id)
{
	if(!val){
		return false;
	}
	var info = $("#"+id).val();
	if(info){
		var lst = info.split('|');
		var is_add = true;
		for(var i in lst){
			if(lst[i] == val){
				is_add = false;
			}
		}
		if(!is_add){
			$.dialog.alert('数据已经使用，不能重复');
			return false;
		}
		val = info + "|"+val;
	}
	$("#"+id).val(val);
	if(id == 'ctrl'){
		update_func(val);
	}
}

function update_func(val)
{
	if(!val || val == 'undefined'){
		val = $("#ctrl").val();
		if(!val){
			return false;
		}
	}
	var url = get_url('rewrite','getfunc','id='+$.str.encode(val));
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			var lst = rs.content;
			html = '<option value="">请选择…</option>';
			for(var i in lst){
				html += '<option value="'+i+'">'+lst[i]+'</option>';
			}
			$("#func_select").html(html);
            form.render('select');
		}
	})
}

