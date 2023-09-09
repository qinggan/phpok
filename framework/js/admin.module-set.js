/**
 * 模块编辑
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年3月3日
**/

$(document).ready(function(){
	layui.use('form',function(){
		var form = layui.form;
		form.on('radio(mtype)',function(data){
			if(data.value == 1){
				$("#tbl_html").hide();
				$("#tbl_single").show();
			}else{
				$("#tbl_html").show();
				$("#tbl_single").hide();
			}
		});
	})
});