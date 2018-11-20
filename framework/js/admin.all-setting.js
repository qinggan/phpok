/**
 * 全局配置页
 * @作者 qinggan <admin@phpok.com>
 * @版权 2008-2018 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年10月25日
**/

function insert_input(val,id,space)
{
	if(!id || id == 'undefined'){
		id = 'rule';
	}
	if(!space || space == 'undefined'){
		space = '';
	}
	var info = $("#"+id).val();
	if(info){
		val = info + space +val;
	}
	$("#"+id).val(val);
}


$(document).ready(function(){
	layui.use(['layer','form','laydate'],function () {
		let form = layui.form;
		form.on('switch(status)', function(data){
			let id = $(this).attr('data');
			if (data.elem.checked) {
				$('#'+id).hide();
			}else{
				$('#'+id).show();
			}
		});
	});
});