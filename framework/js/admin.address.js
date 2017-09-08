/**
 * 地址库中涉及到的 JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月04日
**/

;(function($){
	$.admin_address = {
		order:function(id)
		{
			var opener = $.dialog.opener;
			var url = get_url('address','one','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					var data = rs.info;
					opener.$("#s-fullname").val(data.fullname);
					opener.$("#s-country").val(data.country);
					opener.$("#s-province").val(data.province);
					opener.$("#s-city").val(data.city);
					opener.$("#s-county").val(data.county);
					opener.$("#s-address").val(data.address);
					opener.$("#s-zipcode").val(data.zipcode);
					opener.$("#s-email").val(data.email);
					opener.$("#s-mobile").val(data.mobile);
					opener.$("#s-tel").val(data.tel);
					$.dialog.close();
				}else{
					$.dialog.alert(rs.info);
				}
			})
		}
	}
})(jQuery);

function address_delete(id)
{
	$.dialog.confirm(p_lang('确定要ID为 <span class="red">{id}</span> 的数据吗？<br>删除后地址库信息是不能恢复的',"#"+id),function(){
		var url = get_url('address','delete','id='+id);
		$.phpok.json(url,function(data){
			if(data.status){
				$.dialog.alert(p_lang('地址删除成功'),function(){
					$.phpok.reload();
				},'succeed');
			}else{
				$.dialog.alert(data.info);
			}
		});
	});
}

$(document).ready(function(){
	$("#post_save").submit(function(){
		$(this).ajaxSubmit({
			'url':get_url('address','save'),
			'type':'post',
			'dataType':'json',
			'success':function(rs){
				if(rs.status){
					var id = $("#id").val();
					$.dialog.alert(rs.info,function(){
						$.phpok.go(get_url('address'));
					},'succeed');
				}else{
					$.dialog.alert(rs.info);
					return false;
				}
			}
		});
		return false;
	});
});