/**
 * 后面页面脚本_管理全球国家及州/省信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年05月27日 19时51分
**/
;(function($){
	$.admin_worlds = {
		search:function(pid)
		{
			url = get_url('worlds','');
			if(pid && pid != 'undefined'){
				url += "&parent_id="+pid;
			}
			var status = $("#keywords_status").val();
			if(status){
				url += "&keywords[status]="+status;
			}
			var name = $("#keywords_name").val();
			if(name){
				url += "&keywords[name]="+$.str.encode(name);
			}
			var name_en = $("#keywords_name_en").val();
			if(name_en){
				url += "&keywords[name_en]="+$.str.encode(name_en);
			}
			$.phpok.go(url);
			return false;
		},
		move:function(id)
		{
			var url = get_url('worlds','move');
			var id = $.checkbox.join('#pl_action');
			if(!id){
				$.dialog.alert('未指定要迁移的国家');
				return false;
			}
			var pid = $("#move_country").val();
			if(!pid){
				$.dialog.alert(p_lang('请选择要迁移的洲'));
				return false;
			}
			url += "&ids="+$.str.encode(id)+"&pid="+pid;
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(p_lang('迁移成功…'),function(){
						$.phpok.reload();
					}).time(1).lock();
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		add:function(obj,pid)
		{
			var title = $(obj).attr('data-title');
			var url = get_url('worlds','add');
			if(pid && pid != 'undefined'){
				url += "&pid="+pid;
			}
			$.win(title,url);
		},
		edit:function(id)
		{
			var url = get_url('worlds','edit','id='+id);
			$.win(p_lang('编辑')+"_#"+id,url);
		},
		status:function(id)
		{
			var url = get_url("worlds","status","id="+id);
			$.phpok.json(url,function(data){
				if(!data.status){
					$.dialog.alert(data.info);
					return false;
				}
				var old_value = $("#status_"+id).attr("value");
				var new_value = old_value == '1' ? '0' : '1';
				$("#status_"+id).removeClass('status'+old_value).addClass("status"+new_value).attr('value',new_value);
			})
		},
		status_pl:function(val)
		{
			var id = $.checkbox.join();
			if(!id || id == 'undefined'){
				$.dialog.alert(p_lang('未选中要操作ID'));
				return false;
			}
			var url = get_url('worlds','status_pl','id='+$.str.encode(id)+"&status="+val);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('操作成功')).lock();
				$.phpok.reload();
			});
		},
		taxis:function(obj,id)
		{
			var url = get_url('worlds','taxis','id='+id);
			phpok_taxis(obj,url);
		},
		del:function(id,name,en)
		{

			if(!id || id == 'undefined'){
				var id = $.checkbox.join();
				if(!id){
					$.dialog.alert('请选择要删除的地区');
					return false;
				}
				var t = p_lang('确定要删除选中的地区')
			}else{
				var t = p_lang('确定要删除：{title}',{'title':name+' / '+en});
			}
			$.dialog.confirm(t,function(){
				var url = get_url('worlds','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('删除成功'),function(){
						$.phpok.reload();
					}).time(1).lock();
				});
			});
		},
		continent:function()
		{
			$.dialog.alert('正在制作中，请忽略');
		}
	}
})(jQuery);

$(document).ready(function(){
	if(layui && layui != 'undefined'){
		layui.use('form',function(){
			layui.form.on("select(continent)",function(data){
				if(!data.value){
					return false;
				}
				var url = get_url('worlds','','continent_id='+data.value);
				$.phpok.go(url);
			})
		})
	}
});
