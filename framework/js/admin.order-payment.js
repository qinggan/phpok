/**
 * 支付页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月06日
**/
;(function($){
	$.admin_order_payment = {
		select:function(val)
		{
			if(val == 'other'){
				$("input[name=title]").parent().show();
			}else{
				$("input[name=title]").parent().hide();
			}
		},
		add:function()
		{
			$("#postsave").ajaxSubmit({
				'url':get_url('order','payment_save','id={$rs.id}'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('付款添加成功'),function(){
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
		del:function(id,order_id)
		{
			var url = get_url('order','payment_delete','id='+id+"&order_id="+order_id);
			$.dialog.confirm(p_lang('确定要删除这条支付吗？'),function(){
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('付款信息删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);
$(document).ready(function(){
	layui.use(['form','laydate'],function(){
		var form = layui.form;
		form.on('select(payment)',function(data){
			$.admin_order_payment.select(data.value);
		});
		layui.laydate.render({
            elem: '#dateline'
        });
	});
});