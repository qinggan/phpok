/**
 * 后台用户涉及到的地址
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @日期 2017年05月27日
**/
;(function($){
	$.admin_user = {
		address:function(id)
		{
			var url = get_url('address','open','type=user_id&keywords='+id);
			$.dialog.open(url,{
				'title':p_lang('用户地址'),
				'width':'800px',
				'height':'500px',
				'lock':true
			})
		},
		show_setting:function()
		{
			var url = get_url('user','show_setting');
			$.dialog.open(url,{
				'title':p_lang('用户字段显示设置'),
				'width':'600px',
				'height':'400px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},'okVal':p_lang('提交'),'cancel':true,'cancelVal':p_lang('取消')
			})
		},

		/**
		 * 用户字段快速添加
		**/
		field_quick_add:function(id)
		{
			var url = get_url('user','fields_save','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},

		/**
		 * 用户字段删除
		**/
		field_delete:function(id,title)
		{
			$.dialog.confirm(p_lang('确定要删除字段 {title} 吗？<br>删除后相应的字段内容也会被删除，不能恢复','<span class="red">'+title+'</span>'),function(){
				$.phpok.json( get_url("user","field_delete","id="+id),function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},

		/**
		 * 用户字段编辑
		**/
		field_edit:function(id)
		{
			$.dialog.open(get_url("user","field_edit","id="+id),{
				"title" : p_lang('编辑字段属性'),
				"width" : "700px",
				"height" : "80%",
				"resize" : false,
				"lock" : true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_user.field_save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancel':true
			});
		},

		/**
		 * 用户字段添加
		**/
		field_add:function()
		{
			$.dialog.open(get_url("user","field_edit"),{
				"title" : p_lang('添加用户字段'),
				"width" : "700px",
				"height" : "80%",
				"resize" : false,
				"lock" : true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_user.field_save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancel':true
			});
		},

		/**
		 * 保存扩展字段信息
		**/
		field_save:function()
		{
			var opener = $.dialog.opener;
			var obj = $.dialog.tips(p_lang('正在保存数据…'),100);
			$("#post_save").ajaxSubmit({
				'url':get_url('user','field_edit_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						obj.content(p_lang('数据保存成功'));
						opener.$.phpok.reload();
						return true;
					}
					obj.close();
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},

		save:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('user','setok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(rs.info);
						$.admin.close(get_url('user'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		status:function(id,obj)
		{
			var url = get_url('user','status','id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				var val = rs.info;
				$(obj).removeClass();
				$(obj).addClass("status"+val).attr("value",val);
			});
		},
		vouch:function(id)
		{
			var url =get_url('user','vouch','id='+id);
			$.dialog.open(url,{
				'title':p_lang('推荐的用户')+"_#"+id,
				'lock':true,
				'width':'700px',
				'height':'500px',
				'cancel':true,
				'cancelVal':'关闭'
			})
		},
		wealth_action:function(title,wid,uid,unit)
		{
			var url = get_url('wealth','action_user','wid='+wid+"&uid="+uid);
			$.dialog.open(url,{
				'title':p_lang('用户{title}操作',{'title':title}),
				'lock':true,
				'width':'700px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交保存',
				'cancel':true
			});
		},
		wealth_log:function(title,wid,uid)
		{
			var url = get_url('wealth','log','wid='+wid+"&uid="+uid);
			$.win(p_lang('用户')+title+p_lang('日志')+"_#"+uid,url);
		}
	}
})(jQuery);