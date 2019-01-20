/**
 * 附件分类配置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年12月30日
**/
$(document).ready(function(){
	if($("input[name=gdall]:checked").val() == '1'){
		$('#gdsetting').hide()
	} else {
		$('#gdsetting').show()
	}
	$("#catesubmit").submit(function(){
		$(this).ajaxSubmit({
			'url':get_url('rescate','save'),
			'type':'post',
			'dataType':'json',
			'success':function(rs){
				if(rs.status){
					var tip = $("#id").val() ? p_lang('附件分类编辑成功') : p_lang('附件分类添加成功');
					$.dialog.tips(tip,function(){
						$.admin.reload(get_url('rescate'));
						$.admin.close(get_url('rescate'));
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			}
		});
		return false;
	});
	layui.use('form',function () {
		layui.form.on('radio(gdall)', function(data){
			if(data.value == 1){
				$('#gdsetting').slideUp();
			}else{
				$('#gdsetting').slideDown();
			}
		});
	})

});