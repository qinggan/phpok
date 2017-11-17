/**
 * 项目管理相关JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月07日
**/
;(function($){
	$.admin_project = {

		/**
		 * 模块选择时执行触发
		**/
		module_change:function(obj)
		{
			$("#module_set,#module_set2").hide();
			var val = $(obj).val();
			var mtype = $(obj).find('option:selected').attr('data-mtype');
			if(!val || val == '0'){
				return true;
			}
			$("#tmp_orderby_btn,#tmp_orderby_btn2").html('');
			var c = '';
			if(mtype == 1){
				c += '<input type="button" value="ID" onclick="phpok_admin_orderby(\'orderby2\',\'id\')" class="phpok-btn" />';
			}
			$.phpok.json(get_url('project','mfields','id='+val),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				if(rs.info){
					var list = rs.info;
					for(var i in list){
						if(mtype == 1){
							c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby2\',\''+list[i].identifier+'\')" class="phpok-btn"/>';
						}else{
							c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby\',\'ext.'+list[i].identifier+'\')" class="phpok-btn"/>';
						}
					}
				}
				if(mtype == 1){
					$("#tmp_orderby_btn2").html(c);
					$("#module_set2").show();
				}else{
					$("#tmp_orderby_btn").html(c);
					$("#module_set").show();
				}
				return true;
			});
		}
	}
})(jQuery);

$(document).ready(function(){
	$.admin_project.module_change($("#module")[0]);
});
