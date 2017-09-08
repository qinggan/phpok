/**
 * 回复管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年08月08日
**/
;(function($){
	$.admin_reply = {
		adm:function(id)
		{
			var url = get_url("reply","adm","id="+id);
			$.dialog.open(url,{
				title:p_lang('管理员回复：{id}','<span class="red">#'+id+'</span>')
				, width:"90%"
				, height:"90%"
				, resize:false
				, lock:true
				, ok:function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					var status = iframe.save();
					if(status){
						$.dialog.alert(p_lang('管理员回复操作成功'),function(){
							$.phpok.reload();
						},'succeed');
					}
					return false;
				},okVal:p_lang('管理员回复')
				,cancel:function(){return true;}
			});
		},
		status:function(id,status)
		{
			var url = get_url("reply","status","id="+id+"&status="+status);
			if(status == 0){
				$.dialog.confirm(p_lang('确定要关闭这条评论吗，关闭后前台是不会显示的'),function(){
					var rs = $.phpok.json(url);
					if(rs.status == "ok"){
						$.phpok.reload();
					}else{
						$.dialog.alert(rs.content);
						return false;
					}
				});
			}else{
				var rs = $.phpok.json(url);
				if(rs.status == "ok"){
					$.phpok.reload();
				}else{
					$.dialog.alert(rs.content);
					return false;
				}
			}
		},
		sublist:function(id)
		{
			if($("#comment_reply_"+id).is(":hidden")){
				$("#comment_reply_"+id).show();
				$("#show_hide_c_"+id).val("隐藏评论的回复");
			}else{
				$("#comment_reply_"+id).hide();
				$("#show_hide_c_"+id).val("显示评论的回复");
			}
		},
		del:function(id)
		{
			
			$.dialog.confirm(p_lang('确定要删除ID为{id}的评论吗?删除后是不能恢复的！<br/>如果此评论有回复将一起被删除'," <strong class='red'>"+id+"</strong> "),function(){
				var url = get_url("reply","delete","id="+id);
				var rs = $.phpok.json(url);
				if(rs.status == "ok"){
					$.phpok.reload();
				}else{
					$.dialog.alert(rs.content);
					return false;
				}
			});
		},
		edit:function(id)
		{
			var url = get_url("reply","edit","id="+id);
			$.dialog.open(url,{
				title:p_lang('编辑评论：{id}','<span class="red">#'+id+'</span>')
				, width:"90%"
				, height:"90%"
				, resize:false
				, lock:true
				, ok:function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					var status = iframe.save();
					if(status){
						$.dialog.alert(p_lang('评论信息修改成功'),function(){
							$.phpok.reload();
						},'succeed');
					}
					return false;
				},okVal:p_lang('修改评论')
				,cancel:function(){return true;}
			});
		}
	}
})(jQuery);