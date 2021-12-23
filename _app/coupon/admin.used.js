/**
 * 优惠码历史查询
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年9月4日
**/
;(function($){
	$.admin_coupon_history = {
		search:function()
		{
			var is_error = true;
			$("input[data-name=keywords]").each(function(i){
				var val = $(this).val();
				if(val){
					is_error = false;
				}
			});
			if(is_error){
				$.dialog.alert('请输入要搜索的关键字');
				return false;
			}
			return true;
		},
		del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
			}
			if(!id){
				$.dialog.alert('请选择要删除的使用记录');
				return false;
			}
			$.dialog.confirm("确定要删除记录吗？删除后记录将不可查询",function(){
				var url = get_url('coupon','o_delete','id='+$.str.encode(id));
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips('删除成功').lock(1);
					$.phpok.reload();
				});
			});
		}
	}
})(jQuery);
$(document).ready(function(){
	layui.laydate.render({
		elem: '#keywords_startdate' //指定元素
	});
	layui.laydate.render({
		elem: '#keywords_stopdate' //指定元素
	});
});