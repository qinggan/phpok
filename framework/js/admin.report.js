/**
 * 统计报表涉及到的JS操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月17日
**/
;(function($){
	$.admin_report = {
		select_project:function(val)
		{
			if(!val || val == 'undefined'){
				$("div[data-id=line-x],div[data-id=line-y],div[data-id=line-z]").hide();
				return true;
			}
			if(val && val != 'undefined'){
				var url = get_url('report','ajax_type','type='+val);
				$.phpok.json(url,function(data){
					if(!data.status){
						$.dialog.alert(data.info);
						return false;
					}
					if(!data.info){
						$("div[data-id=line-x],div[data-id=line-y]").addClass('hide');
						return true;
					}
					if(data.info.x){
						var x = data.info.x;
						var xhtml = '<option value="">'+p_lang('请选择…')+'</option>';
						for(var i in x){
							xhtml += '<option value="'+i+'">'+x[i]+'</option>';
						}
						$("div[data-id=line-x] select").html(xhtml);
						$("div[data-id=line-x]").removeClass('hide');
					}
					if(data.info.y){
						var y = data.info.y;
						var html = template('line-y-html', {
						    ylist: data.info.y
						});
						$("div[data-id=line-y]").html(html).removeClass('hide');
						if(layui && layui.form){
							layui.form.render();
						}
					}
					if(data.info.z){
						$("div[data-id=line-z]").removeClass('hide');
					}else{
						$("div[data-id=line-z]").addClass('hide');
					}
				});

				layui.form.render()
			}
		}
	}
	$(document).ready(function(){
		layui.use(['laydate','form'],function () {
	        layui.laydate.render({elem:'#startdate'});
	        layui.laydate.render({elem:'#stopdate'});
	        layui.form.on('select(type)',function (data) {
	            $.admin_report.select_project(data.value);
	            window.setTimeout("layui.form.render()",200);
	        })
	    });
	});
})(jQuery);


