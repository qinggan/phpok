/**
 * 区域设置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月27日
**/
function save()
{
	var title = $("#title").val();
	if(!title){
		$.dialog.alert('名称不能为空');
		return false;
	}
	var info = $.input.checkbox_join();
	if(!info){
		$.dialog.alert('请选择相应的省市');
		return false;
	}
	var opener = $.dialog.opener;
	$("#post_save").ajaxSubmit({
		'url':get_url('freight','zone_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.alert('操作成功',function(){
					opener.$.phpok.reload();
				},'succeed');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
	return true;
}
function update_city(pro)
{
	var p = false;
	$('input[data=city'+pro+']').each(function(i){
		if($(this).prop('checked')){
			p = true;
		}
	});
	if(p == true){
		$("input[data=pro"+pro+"]").prop('checked',true);
	}else{
		$("input[data=pro"+pro+"]").prop('checked',false);
	}
}
function update_pro(pro)
{
	var t = $("input[data=pro"+pro+"]").prop('checked');
	if(t){
		$('input[data=city'+pro+']').prop('checked',true);
	}else{
		$('input[data=city'+pro+']').prop('checked',false);
	}
}
