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
	var info = $.checkbox.join();
	if(!info){
		$.dialog.alert('请选择相应的省市');
		return false;
	}
	var fid = $("#fid").val();
	$("#post_save").ajaxSubmit({
		'url':get_url('freight','zone_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.alert('操作成功',function(){
					$.admin.close(get_url('freight','zone','fid='+fid));
				},'succeed');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
	return false;
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
function update_province_city()
{
	var url = get_url('freight','province_city');
	var tid = $("#id").val();
	if(tid && tid != 'undefined'){
		url += "&id="+tid;
	}
	var country_id = $("#country_id").val();
	if(!country_id){
		$.dialog.alert(p_lang('请选择国家'));
		return false;
	}
	url += "&country_id="+country_id;
	$.phpok.json(url,function(rs){
		$("#province_city").html(rs.info);
		return true;
	});
}
$(document).ready(function(){
	layui.form.on('select(continent)', function(data){
		var name = "countrylist-"+data.value;
		var html = '<option value="">'+p_lang('请选择国家…')+'</option>';
		$("input[data-name="+name+"]").each(function(i){
			html += '<option value="'+$(this).attr("data-id")+'">'+$(this).val()+'</option>';
		});
		$("#country_id").html(html);
		layui.form.render("select");
	}); 
	layui.form.on("select(country)",function(data){
		if(data.value){
			update_province_city();
		}
	});
	var country_id = $("#country_id").val();
	if(country_id && country_id != 'undefined'){
		update_province_city();
	}
});