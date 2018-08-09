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
				$("li[data-id=line-x],li[data-id=line-y],li[data-id=line-z]").hide();
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
						$("li[data-id=line-x],li[data-id=line-y]").hide();
						return true;
					}
					if(data.info.x){
						var x = data.info.x;
						var xhtml = '<select name="x"><option value="">'+p_lang('请选择…')+'</option>';
						for(var i in x){
							xhtml += '<option value="'+i+'">'+x[i]+'</option>';
						}
						xhtml += '</select>';
						$("li[data-id=line-x]").html(xhtml);
						$("li[data-id=line-x]").show();
					}
					if(data.info.y){
						var y = data.info.y;
						var yhtml = '<ul class="layout">';
						//var yhtml = '<select name="x"><option value="">'+p_lang('请选择统计项目…')+'</option>';
						for(var i in y){
							yhtml += '<li><label><input type="checkbox" name="y[]" value="'+i+'"/>'+y[i]+'</label></li>';
						}
						yhtml += '</ul>';
						$("li[data-id=line-y]").html(yhtml);
						$("li[data-id=line-y]").show();
					}
					if(data.info.z){
						$("li[data-id=line-z]").show();
					}else{
						$("li[data-id=line-z]").hide();
					}
				});
			}
		}
	}
})(jQuery);

$(document).ready(function(){
	laydate.render({elem:'#startdate'});
	laydate.render({elem:'#stopdate'});
});