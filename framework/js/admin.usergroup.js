/**
 * 会员组
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年12月23日
**/
;(function($){
	$.admin_usergroup = {
		del:function(id)
		{
			if(!id || id == 'undefined'){
				$.dialog.alert(p_lang('操作非法'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要删除此会员组吗？删除后是不能恢复的'),function(){
				var url = get_url("usergroup","delete","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('会员组删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		modify:function(id)
		{
			$.win(p_lang('编辑会员组')+"_#"+id,get_url("usergroup","set","id="+id));
		},
		add:function()
		{
			$.win(p_lang('添加会员组'),get_url("usergroup","set"));
		},
		set_default:function(id,title)
		{
			var tip = p_lang('确定要将组{title}设置为会员默认组吗?<br />设置成功后，新注册会员将自定使用此组功能',' <span class="red">'+title+'</span> ');
			$.dialog.confirm(tip,function(){
		        var url = get_url("usergroup","default","id="+id);
		        $.phpok.json(url,function(rs){
			        if(rs.status){
				        $.dialog.tips(p_lang('默认组设置成功'),function(){
					        $.phpok.reload();
				        }).lock();
				        return true;
			        }
			        $.dialog.alert(rs.info);
			        return false;
		        });
		    });
		},
		guest:function(id,title)
		{
			var tip = p_lang("确定要将组{title}设置为游客组吗?<br />设置成功后，来访者将调用此组权限信息"," <span class='red'>"+title+"</span> ");
			$.dialog.confirm(tip,function(){
				var url = get_url("usergroup","guest","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
				        $.dialog.tips(p_lang('游客组设置成功'),function(){
					        $.phpok.reload();
				        }).lock();
				        return true;
			        }
			        $.dialog.alert(rs.info);
			        return false;
				});
			});
		},
		status:function(id)
		{
			var val = $("#status_"+id).val();
		    if(val == 1){
			    var tip = p_lang("确定要禁用此会员组信息吗?<br />禁用后，该组会员不能登录，请慎用");
		        $.dialog.confirm(tip,function(){
		            var url = get_url("usergroup","status","id="+id+"&status=0");
		            $.phpok.json(url,function(rs){
			            if(rs.status){
				            $.dialog.tips(p_lang('会员组已禁用'),function(){
								$.phpok.reload();
							}).lock();
							return true;
			            }
			            $.dialog.alert(rs.info);
			            return false;
		            });
		        });
		        return true;
		    }
		    var url = get_url("usergroup","status","id="+id+"&status=1");
		    $.phpok.json(url,function(rs){
			    if(rs.status){
		            $.dialog.tips(p_lang('会员组启用成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
	            }
	            $.dialog.alert(rs.info);
	            return false;
		    });
		},
		setok:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('usergroup','setok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(rs.info,function(){
							$.admin.reload(get_url('usergroup'));
							$.admin.close(get_url('usergroup'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);

