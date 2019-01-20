/**
 * 附件分类管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年12月30日
**/
;(function($){
	$.admin_rescate = {
		del:function(id,title)
		{
			var tip = p_lang('确定要删除这个附件分类吗？{title}','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
	            var url = get_url('rescate','delete','id='+id);
	            $.phpok.json(url,function(rs){
	                if(rs.status == 'ok'){
	                    $.dialog.alert('删除成功',function(){
	                        $.phpok.reload();
	                    },'succeed');
	                }else{
	                    $.dialog.alert(rs.content);
	                    return false;
	                }
	            });
	        });
		},
		etypes_info:function(id)
		{
			if(!id || id == 'undefined'){
				id = $("#")
			}
		}
	}
})(jQuery);