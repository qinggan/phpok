/**************************************************************************************************
	文件： {phpok}/js/admin.index.js
	说明： 后台首页涉及到的样式
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2014年9月24日
***************************************************************************************************/
//添加站点信息
function add_site()
{
	var url = get_url('all','add');
	$.dialog.open(url,{
		'title': p_lang('添加站点')
		,'lock': true
		,'width': '500px'
		,'height': '300px'
		,'resize': false
	});
}

function phpok_admin_control()
{
	var url = get_url("me","setting");
	$.dialog.open(url,{
		"title":p_lang('修改管理员信息'),
		"width":600,
		"height":500,
		"lock":true,
		'move':false,
		'is_max':false
	});
}


function update_select_lang(val)
	{
		var url = get_url("index",'','_langid='+val);
		$.phpok.go(url);
	}
	function phpok_admin_logout()
	{
		$.dialog.confirm(p_lang('确定要退出吗？'),function(){
			var url = get_url("logout");
			$.phpok.go(url);
		});
	}
	function phpok_admin_clear()
	{
		var url = get_url("index","clear");
		var rs = $.phpok.json(url);
		if(rs.status == "ok"){
			$.dialog.alert(p_lang('缓存清空完成'));
		}else{
			$.dialog.alert(rs.content);
		}
	}

$(document).ready(function(){
	//判断是否显示
	$(window).click(function(e){
		var e = e || window.event;
		var obj = e.srcElement || e.target;
		if(obj.id == 'top-menu-a')
		{
			var is_hidden = $("#top-menu").is(":hidden");
			if(is_hidden)
			{
				$('#top-menu').show();
			}
			else
			{
				$('#top-menu').hide();
				$(".second_ul").hide();
			}
		}
		else
		{
			$('#top-menu').hide();
			$(".second_ul").hide();
		}
	});

	$("li[name=subtree]").mouseover(function(){
		$(".second_ul").hide();
		$(".second_ul",this).show();
	});
});