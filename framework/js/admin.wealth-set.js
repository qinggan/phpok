/**
 * 设置页样式
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年4月1日
**/
$(document).ready(function(){
	if(layui && layui != 'undefined'){
		layui.use('form',function(){
			var form = layui.form;
			form.on('radio(ifpay)',function(data){
				if(data.value == '1'){
					$('#ratio_li').show()
				}else{
					$('#ratio_li').hide()
				}
			});
			form.on('radio(ifcash)',function(data){
				if(data.value == '1'){
					$('#ratio2_li').show()
				}else{
					$('#ratio2_li').hide()
				}
			});
		})
	}
});