/**
 * 物流快递
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年10月07日
**/
;(function($){
	$.admin_order_express = {
		save:function()
		{
			$("#postsave").ajaxSubmit({
				'url':get_url('order','express_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('物流信息添加成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		del:function(id)
		{
			var tip = p_lang('确定要删除这条物流信息吗？删除后相应记录会被删除');
			$.dialog.confirm(tip,function(){
				var url = get_url('order','express_delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		remote:function(id)
		{
			var url = api_url('express','remote','id='+id);
			var tip = $.dialog.tips('正在获取数据，请稍候…',100);
			$.phpok.json(url,function(rs){
				tip.close();
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);