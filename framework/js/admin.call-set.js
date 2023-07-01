/**
 * 数据调用中心涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月10日
**/

$(document).ready(function(){
	//根据选项进行Ajax操作
	layui.form.on("select(type_id)",function(data){
		$.admin_call.type_id(data.value);
	});
	layui.form.on("select(pid)",function(data){
		$.admin_call.update_param(data.value);
	});
});