/**
 * 项目编辑时有效处理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年03月10日
**/
$(document).ready(function(){
	//完善扩展分类设置
	var emailtpl = new Array();
	$("input[data-name=emailtpl]").each(function(i){
		var id = $(this).val();
		var name = $(this).attr("data-title");
		var note = $(this).attr("data-note");
		var orderby = parseInt($(this).attr("data-orderby"));
		emailtpl[i] = {'id':id,'name':name,'note':note,'orderby':orderby};
	});
	$('#etpl_admin,#etpl_user,#etpl_comment_admin,#etpl_comment_user').selectPage({
	    showField : 'name',
	    keyField : 'id',
	    selectOnly : true,
	    pagination : false,
	    listSize : 999,
	    multiple : false,
	    data : emailtpl,
	    orderBy:['orderby'],
	    multipleControlbar:false,
	    formatItem : function(data){
		    if(data.note){
			    return data.name + '<span class="gray i"> / '+data.note+"</span>";
		    }
		    return data.name;
	    }
	});
	$.admin_project.module_change($("#module")[0]);
});