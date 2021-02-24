/**
 * 运费添加/编辑操作
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年9月18日
**/

function save()
{
	var obj = $.dialog.tips(p_lang('正在保存数据，请稍候…')).lock();
	$("#post_save").ajaxSubmit({
		'url':get_url('freight','save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			obj.close();
			if(rs.status){
				var tip = $("#id").length>0 ? p_lang('运费编辑成功') : p_lang('运费添加成功');
				$.dialog.alert(tip,function(){
					$.admin.close(get_url('freight'));
				},'succeed');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
	return false;
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
});