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
					iframe.$.admin_reply.adm_save();
					return false;
				},okVal:p_lang('管理员回复')
				,cancel:true
			});
		},
		adm_save:function()
		{
			var opener = $.dialog.opener;
			var lock_status = $.dialog.tips(p_lang('正在保存数据，请稍候…')).lock();
			$("#post_save").ajaxSubmit({
				'url':get_url('reply','adm_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					lock_status.close();
					if(rs.status){
						$.dialog.tips('操作成功',function(){
							opener.$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		status:function(id,status)
		{
			$.phpok.json(get_url("reply","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					$.phpok.message('pendding');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		del:function(id,ifadm)
		{
			if(ifadm && ifadm != 'undefined'){
				var tip = p_lang('确定要删除这条管理员回复信息吗？');
			}else{
				var tip = p_lang('确定要删除ID为{id}的评论吗?删除后是不能恢复！<br/>评论有回复将一起被删除'," <strong class='red'>"+id+"</strong> ");
			}
			$.dialog.confirm(tip,function(){
				var url = get_url("reply","delete","id="+id);
				var rs = $.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.message('pendding');
						if(ifadm && ifadm != 'undefined'){
							$("#adm_reply_"+id).remove();
						}else{
							$("tr[data-id=replylist_"+id+"]").remove();
						}
						$.dialog.tips(p_lang('操作成功')).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		/**
		 * 附件预览
		**/
		preview_attr:function(id)
		{
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':p_lang('预览附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':false,
				'button': [{
					'name': p_lang('下载原文件'),
					'callback': function () {
						$.phpok.open(get_url('res','download','id='+id));
						return false;
					},
				}],
				'okVal':p_lang('关闭'),
				'ok':true
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
					iframe.$.admin_reply.edit_ok();
					return false;
				},okVal:p_lang('修改评论')
				,cancel:true
			});
		},
		edit_ok:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('reply','edit_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.phpok.message('pendding');
						$.dialog.tips(p_lang('操作成功'),function(){
							opener.$.phpok.reload();
						}).lock();
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