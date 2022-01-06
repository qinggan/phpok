/**
 * 后台管理收藏夹的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
;(function($){
	$.admin_fav = {
		del:function(id)
		{
			$.dialog.confirm(p_lang('确认要删除该收藏主题（ID：{id}）吗？',id),function(){
				$.phpok.json(get_url('fav','delete','id='+id),function(data){
					if(data.status){
						$("tr[data-id="+id+"]").remove();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	}
	$(document).ready(function(){
		layui.use(['laydate','form'],function () {
	        layui.laydate.render({elem:'#startdate'});
	        layui.laydate.render({elem:'#stopdate'});
	    });
	});
})(jQuery);