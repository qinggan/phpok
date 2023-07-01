/**
 * 回复管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年08月08日
**/
;(function($){
	$.admin_reply = {
		adm:function(id)
		{
			var title = p_lang('管理员回复_#{id}',id);
			var url = get_url("reply","adm","id="+id);
			$.win(title,url);
		},
		adm_save:function(obj)
		{
			var lock_status = $.dialog.tips(p_lang('正在保存数据，请稍候…'),1000).lock();
			$(obj).ajaxSubmit({
				'url':get_url('reply','adm_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						lock_status.setting('close',function(){
							$.phpok.reload();
						});
						lock_status.content(p_lang('操作成功')).time(2);
						return true;
					}
					lock_status.content(rs.info).time(2);
					return false;
				}
			});
			return false;
		},
		sublist:function(id)
		{
			//
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
		status_pl:function(status)
		{
			var ids = $.checkbox.join('.ids');
			if(!ids){
				$.dialog.alert(p_lang('未指定要操作的主题'));
				return false;
			}
			url = get_url('reply','status_pl','id='+$.str.encode(ids)+"&status="+status);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('操作成功').lock();
				$.phpok.reload();
			})
		},
		delete_pl:function()
		{
			var ids = $.checkbox.join('.ids');
			if(!ids){
				$.dialog.alert(p_lang('未指定要操作的主题'));
				return false;
			}
			url = get_url('reply','delete','id='+$.str.encode(ids));
			$.dialog.confirm('确定要删除选中的回复吗？'+ids,function(){
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						return false;
					}
					$.dialog.tips('删除成功').lock();
					$.phpok.reload();
				})
			})
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
			$.win(p_lang('编辑评论_#{id}',id),url);
		},
		edit_ok:function()
		{
			var tip = $.dialog.tips('正在保存数据，请稍候…',1000).lock();
			$("#post_save").ajaxSubmit({
				'url':get_url('reply','edit_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						tip.content(p_lang('操作成功')).time(1.5);
						return true;
					}
					tip.content(rs.info).time(2);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);