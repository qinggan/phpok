/**
 * 订单产品保存操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月03日
**/
function save()
{
	var opener = $.dialog.opener;
	$("#post_save").ajaxSubmit({
		'url':get_url('order','product_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.close();
				$.dialog.tips(p_lang('产品信息操作成功'),function(){
					opener.$.admin_order_set.product_reload();
				}).lock();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
}

function load_product(id)
{
	var url = get_url('order','product','id='+id);
	var currency_id = $("#currency_info").attr("data-id");
	if(currency_id){
		url += '&currency_id='+currency_id;
	}
	
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		$("input[name=tid]").val(id);
		$("input[name=title]").val(rs.info.title);
		$("input[name=price]").val(rs.info.price);
		$("input[name=qty]").val(1);
		$("input[name=unit]").val(rs.info.unit);
		$("input[name=weight]").val(rs.info.weight);
		$("input[name=volume]").val(rs.info.volume);
		$("input[name=thumb]").val(rs.info.thumb);
		if(rs.info.is_virtual){
			$("input[name=is_virtual][value=1]").click();
		}else{
			$("input[name=is_virtual][value=0]").click();
		}
		return true;
	});
}