/**
 * 常用字段管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年06月13日
**/
;(function($){
	$.admin_fields = {
		set:function(id)
		{
			var title = p_lang('添加新字段');
			var url = get_url('fields','set');
			if(id && id != 'undefined'){
				url = get_url('fields','set','id='+id);
				title = p_lang('编辑字段：#{id}',id);
			}
			$.win(title,url);
			return true;
		},
		loading:function(id,eid)
		{
			$("#form_type_ext").html('').hide();
			if(!id || id == 'undefined'){
				return false;
			}
			var url = get_url('fields','config','id='+id);
			if(eid && eid != 'undefined'){
				url += "&eid="+eid;
			}
			$.phpok.ajax(url,function(data){
				if(data && data != 'exit'){
					$("#form_type_ext").html(data).show();
				}
			})
		},
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除常用字段 #{id} 吗？','<span class="red">'+id+'</span>'),function(){
				var url = get_url('fields','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
					}else{
						$.dialog.alert(data.info);
						return true;
					}
				})
			});
		},
		filetoinput:function(val,input)
		{
			if(val.substr(0,1) == '/'){
				val = val.substr(1);
			}
			var opener = $.dialog.opener;
			opener.$("#"+input).val(val);
			$.dialog.close();
			return true;
		}
	}
})(jQuery);