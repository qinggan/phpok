/**************************************************************************************************
	文件： js/global.js
	说明： 前台通用JS页
	版本： 4.0
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2016年03月29日
***************************************************************************************************/
function kfonline()
{
	$.dialog({
		'content':$("#popupkf")[0],
		'title':'在线客服',
		'padding':0,
		'lock':true
	});
}

function fav_add(id,obj)
{
	var val = ($(obj).val()).trim();
	if(val == '已收藏'){
		$.dialog.alert('已收藏过，不能重复执行');
		return false;
	}
	var url = api_url('fav','add','id='+id);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			$(obj).val('加入收藏成功');
			window.setTimeout(function(){
				$(obj).val('已收藏')
			}, 1000);
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

;(function($){
	$.user = {
		logout: function(title,homeurl){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您确定要退出吗？',function(){
				var url = api_url('logout');
				var rs = $.phpok.json(url);
				if(rs.status == 'ok'){
					$.dialog.alert('您已成功退出',function(){
						$.phpok.go(homeurl);
					},'succeed');
				}else{
					if(!rs.content){
						rs.content = '退出失败，请检查';
					}
					$.dialog.alert(rs.content,'','error');
					return false;
				}
			});
		}
	};
})(jQuery);


$(document).ready(function(){
	$.mobile.ajaxEnabled = false;
	$.cart.total();
});