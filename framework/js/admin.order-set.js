/**
 * 编辑订单
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月29日
**/
;(function($){
	$.admin_order_set = {
		add:function()
		{
			var url = get_url('order','product_set');
			if($("#id").val()){
				url += "&order_id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				if(!currency_id){
					$.dialog.alert(p_lang('请选择货币类型'));
					return false;
				}
				url += "&currency_id="+currency_id;
			}
			$.dialog.open(url,{
				'title':p_lang('产品添加'),
				'lock':true,
				'width':'760px',
				'height':'500px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		edit:function(id)
		{
			var url = get_url('order','product_set','id='+id);
			if($("#id").val()){
				url += "&order_id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				if(!currency_id){
					$.dialog.alert(p_lang('请选择货币类型'));
					return false;
				}
				url += "&currency_id="+currency_id;
			}
			$.dialog.open(url,{
				'title':p_lang('编辑产品'),
				'lock':true,
				'width':'760px',
				'height':'500px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		del:function(id)
		{
			var self = this;
			$.dialog.confirm(p_lang('确定要删除该产品吗？'),function(){
				var url = get_url('order','product_delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('删除操作成功，请稍候…'));
						self.product_reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		get_price:function()
		{
			var self = this;
			var url = get_url('order','product_price');
			if($("#id").val()){
				url += "&id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				url += "&currency_id="+currency_id;
			}
			var act = $.dialog.tips(p_lang('正在计算价格，请稍候…'),10).lock();
			$.phpok.json(url,function(rs){
				act.close();
				if(rs.status){
					$("#ext_price_product").val(rs.info);
					self.total_price();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		total_price:function()
		{
			var total = 0;
			$("input[sign=ext_price]").each(function(i){
				var val = $(this).val();
				val = parseFloat(val);
				if(isNaN(val)){
					val = 0;
				}
				if($(this).attr("action") == 'add'){
					total += val;
				}else{
					total -= val;
				}
			});
			total = total.toFixed(2);
			$('#price').val(total.toString());
		},
		product_reload:function()
		{
			var self = this;
			var url = get_url('order','productlist');
			var id = $("#id").val();
			if(id){
				url += "&id="+id;
			}else{
				var currency_id = $("#currency_id").val();
				if(currency_id){
					url += "&currency_id="+currency_id;
				}
			}
			var tip = $.dialog.tips("正在加载产品信息，请稍候…",30).lock();
			$.phpok.json(url,function(data){
				tip.close();
				if(data.status){
					$("#product_info").html(data.info);
					layui.use('form',function () {
						layui.form.render();
					});
					self.get_price();
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		copy:function(from,to)
		{
			$("#"+to+"-fullname").val($("#"+from+"-fullname").val());
			$("#"+to+"-firstname").val($("#"+from+"-firstname").val());
			$("#"+to+"-lastname").val($("#"+from+"-lastname").val());
			$("#"+to+"-country").val($("#"+from+"-country").val());
			$("#"+to+"-province").val($("#"+from+"-province").val());
			$("#"+to+"-city").val($("#"+from+"-city").val());
			$("#"+to+"-county").val($("#"+from+"-county").val());
			$("#"+to+"-address").val($("#"+from+"-address").val());
			$("#"+to+"-address2").val($("#"+from+"-address2").val());
			$("#"+to+"-zipcode").val($("#"+from+"-zipcode").val());
			$("#"+to+"-tel").val($("#"+from+"-tel").val());
			$("#"+to+"-mobile").val($("#"+from+"-mobile").val());
			$("#"+to+"-email").val($("#"+from+"-email").val());
		}
	}
})(jQuery);
$(document).ready(function(){
	if(!$("#id").val()){
		$.admin_order.sn();
		$.admin_order.pass();
	}
	if(!$("#passwd").val()){
		$.admin_order.pass();
	}
	$.admin_order_set.product_reload();
});