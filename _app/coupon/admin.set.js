/**
 * 后台-优化惠编辑或添加动作涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月3日
**/
function save()
{
	$("#post_save").ajaxSubmit({
		'url':get_url('coupon','save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				var id = $("#id").val();
				var tip = (id && id != 'undefined') ? p_lang('编辑成功') : p_lang('优惠码添加成功');
				$.dialog.tips(tip,function(){
					$.admin.reload(get_url('coupon'));
					$.admin.close(get_url('coupon'));
				}).lock();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
	return false;
}

$(document).ready(function(){
	layui.use('form',function(){
		var form = layui.form;
		form.on('select(pid)',function(data){
			if(!data.value || data.value == '0'){
				$("#cateid_html").hide();
				$("#cateid").val('');
				return true;
			}
			var url = get_url('call','cate_list','id='+data.value);
			$.phpok.json(url,function(rs){
				if(rs.status){
					var list = rs.info.catelist;
					var html = '<option value="">'+p_lang('请选择…')+'</option>';
					for(var i in list){
						html += '<option value="'+list[i].id+'">'+list[i]._space+''+list[i].title+'</option>';
					}
					$("#cateid").html(html);
					$("#cateid_html").show();
					form.render('select');
					return true;
				}
				$("#cateid_html").hide();
				$("#cateid").val('');
				return true;
			});
		});
		form.on('radio(types)',function(data){
			if(data.value == 'list'){
				$("#types_list").show();
			}else{
				$("#types_list").hide();
			}
		});
		form.on('radio(rate)',function(data){
			if(data.value == 'date'){
				$("#rate_date").show();
			}else{
				$("#rate_date").hide();
			}
		});
		form.on('radio(activity_time)',function(data){
			if(data.value == 'all'){
				$("#activity_time_user").hide();
			}else{
				$("#activity_time_user").show();
			}
		});
		form.on('select(continent)', function(data){
			var name = "countrylist-"+data.value;
			var html = '<option value="">'+p_lang('请选择国家…')+'</option>';
			$("input[data-name="+name+"]").each(function(i){
				html += '<option value="'+$(this).attr("data-id")+'">'+$(this).val()+'</option>';
			});
			$("#country_id").html(html);
			layui.form.render("select");
		});
	});

});