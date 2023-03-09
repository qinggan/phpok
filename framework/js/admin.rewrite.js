/**
 * 伪静态页操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月03日
**/
;(function($){
	$.admin_rewrite = {
		taxis:function(val,id)
		{
			url = get_url('rewrite','taxis','id='+id+"&sort="+val);
			$.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('排序变更成功，请手动刷新'));
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		},
		add:function()
		{
			var url = get_url('rewrite','set');
			$.phpok.go(url);
		},
		edit:function(id)
		{
			var url = get_url('rewrite','set','id='+id);
			$.phpok.go(url);
		},
		del:function(id,title)
		{
			layer.confirm(p_lang('确定要删除这条规则吗？{title}',"<span class='red'>"+title+"</span>"),function(){
				var url = get_url('rewrite','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						layer.msg(p_lang('规则删除成功'));
						$("#edit_"+id).remove();
						return true;
					}
					layer.alert(rs.info);
					return false;
				});
			});
		},
		copy:function(id)
		{
			var url = get_url('rewrite','copy','id='+id);
			var rs = $.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('复制伪静态页链接成功'));
					setTimeout(function () {
						$.phpok.reload();
                    },500);
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		layer.prompt(p_lang('请填写新的排序'),function(val){
			if(val != oldval){
				$.admin_rewrite.taxis(val,id);
			}
		},oldval);
	});
});