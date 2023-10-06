/**
 * 项目编辑时有效处理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2018年03月10日
**/
function cate_add(title)
{
	var url = get_url('cate',"add");
	$.dialog.open(url,{
		"title":title,
		"width":"700px",
		"height":"300px",
		"lock":true,
		"win_max":false,
		"win_min":false,
		'move':false
	});
}

function set_biz()
{
	var status = $("#is_biz").is(':checked');
	if(status){
		$("#use_biz_setting").show();
	}else{
		$("#use_biz_setting").hide();
	}
}

function set_post_status()
{
	var status = $("#post_status").is(':checked');
	if(status){
		$("#email_set_post_status").show();
		$("li[name=f_post]").show();
	}else{
		$("#email_set_post_status").hide();
		$("li[name=f_post]").find('input').attr("checked",false);
		$("li[name=f_post]").hide();
	}
}

function set_comment_status()
{
	var status = $("#comment_status").is(':checked');
	if(status){
		$("#email_set_comment_status").show();
		$("li[name=f_reply]").show();
	}else{
		$("#email_set_comment_status").hide();
		$("li[name=f_reply]").find('input').attr("checked",false);
		$("li[name=f_reply]").hide();
	}
}

function set_attr()
{
	var status = $("#is_attr").is(':checked');
	if(status){
		$("#is_attr_status").show();
	}else{
		$("#is_attr_status").hide();
	}
}

function refresh_catelist()
{
	$.phpok.json(get_url("project","rootcate"),function(rs){
		if(rs.status == "ok"){
			var info = '<option value="0">'+p_lang('不关联分类')+'</option>';
			var lst = rs.content;
			for(var i in lst){
				info += '<option value="'+lst[i]['id']+'">'+lst[i]['title']+'</option>';
			}
			$("#cate,#cate2").html(info);
		}
	});
}

function update_show_select(val)
{
	if(val && val != 'undefined' && val != '0'){
		$("#cate_multiple_set").show();
	}else{
		$("#cate_multiple_set").hide();
	}
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

$(document).ready(function(){
	$.admin_project.module_change($("#module")[0]);
	layui.use('form',function(){
		var form = layui.form;
		form.render();
		form.on('checkbox', function(data){
			if(data.elem.checked){
				$(data.elem).attr("checked",true);
			}else{
				$(data.elem).removeAttr("checked");
			}
			if(data.elem.id == 'post_status'){
				set_post_status();
			}
			if(data.elem.id == 'comment_status'){
				set_comment_status();
			}
			if(data.elem.id == 'is_biz'){
				set_biz();
			}
			if(data.elem.id == 'is_attr'){
				set_attr()
			}
		});
		form.on('select(module)',function(data){
			$.admin_project.module_change(data.elem);
		});
		form.on('select(cate-select)',function(data){
			update_show_select(data.value);
		})
	});
});