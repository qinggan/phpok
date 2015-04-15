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
		'title': '添加站点'
		,'lock': true
		,'width': '500px'
		,'height': '300px'
		,'resize': false
	});
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