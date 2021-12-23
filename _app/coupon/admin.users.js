/**
 * 已领取的优惠券搜索
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年12月7日
**/
;(function($){
	$.admin_coupon_user = {
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
		clear_users_expire:function()
		{
			$.dialog.confirm('确定要清除已失效的优惠券吗？',function(){
				var url = get_url('coupon','u_clear');
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips('清理完成').time(1);
					$.phpok.reload();
				})
			});
		},
		del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
			}
			if(!id){
				$.dialog.alert('请选择要删除的优惠券');
				return false;
			}
			$.dialog.confirm("确定要优惠券吗？删除后用户不能直接使用，需要重新领取",function(){
				var url = get_url('coupon','u_delete','id='+$.str.encode(id));
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