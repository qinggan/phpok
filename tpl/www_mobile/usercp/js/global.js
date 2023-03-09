/**
 * 个人中心脚本执行
 * @作者 qinggan <admin@phpok.com>
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2021年5月14日
**/

/**
 * 在线充值支付
**/
function recharge_payment_submit(obj)
{
	var w = $(obj).find("select[name=wealth]").val();
	if(!w){
		$.dialog.alert(p_lang('请选择充值目标'));
		return false;
	}
	var price = $(obj).find("input[name=price]").val();
	if(!price || price == 'undefined'){
		$.dialog.alert(p_lang('请输入要充值的金额'));
		return false;
	}
	if(parseFloat(price)<0.01){
		$.dialog.alert(p_lang('充值金额不能小于0.01元'));
		return false;
	}
	var p = $(obj).find("input[name=payment]:checked").val();
	if(!p || p == 'undefined'){
		$.dialog.alert(p_lang('请选择充值方式'));
		return false;
	}
	var iframe = $(obj).find("input[name=payment]:checked").attr("data-iframe");
	if(iframe == 1){
		var title = $(obj).find("input[name=payment]:checked").attr("data-title");
		var url = api_url('payment','create','type=recharge');
		url += "&wealth="+w+"&price="+price+"&payment="+p;
		$.phpok.json(url,function(rs){
			if(!rs.status){
				$.dialog.alert(rs.info);
				return false;
			}
			var url = get_url("payment","qrcode","id="+rs.info);
			$.dialog.open(url,{
				'title':title,
				'width':'500px',
				'height':'600px',
				'lock':true
			});
		});
		return false;
	}
	var url = get_url('payment','create','type=recharge');
	url += "&wealth="+w+"&price="+price+"&payment="+p;
	$.phpok.open(url);
	return false;
}


