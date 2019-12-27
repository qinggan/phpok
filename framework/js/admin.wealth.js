/**
 * 财富
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年3月30日
**/
;(function($){
	$.admin_wealth = {
		status:function(id)
		{
			var url = get_url('wealth','status','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					var oldvalue = $("#status_"+id).attr('value');
					var old_class = 'status'+oldvalue;
					var new_class = 'status'+rs.info;
					$("#status_"+id).removeClass(old_class).addClass(new_class).attr('value',rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		del:function(id)
		{
			var tip = p_lang('确定要删除当前财富方案吗？删除后不能恢复，请慎重考虑！');
			$.dialog.confirm(tip,function(){
				var url = get_url('wealth','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('删除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		set:function(id)
		{
			var url = get_url('wealth','set');
			var title = p_lang('添加财富方案');
			if(id && id != 'undefined'){
				url = get_url('wealth','set','id='+id);
				title = p_lang('编辑财富方案')+"_#"+id;
			}
			$.win(title,url);
		},
		rule:function(id)
		{
			if(!id || id == 'undefined'){
				$.dialog.alert('未指定ID');
				return false;
			}
			var url = get_url('wealth','rule','id='+id);
			$.win(p_lang('财富规则')+"_#"+id,url);
		},
		info:function(id)
		{
			if(!id || id == 'undefined'){
				$.dialog.alert('未指定ID');
				return false;
			}
			var url = get_url('wealth','info','id='+id);
			$.win(p_lang('财富记录')+"_#"+id,url);
		},
		save:function()
		{
			$("#pay_submit").ajaxSubmit({
				'url':get_url('wealth','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('操作成功'),function(){
							$.admin.reload(get_url('wealth'));
							$.admin.close(get_url('wealth'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		log:function(id,uid)
		{
			var url = get_url('wealth','log','wid='+id+"&uid="+uid);
			var title = p_lang('财富日志')+"_#"+uid;
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'500px',
				'height':'400px',
				'ok':true,
				'okVal':'关闭'
			});
		},
		act:function(id,uid,type,title,unit)
		{
			var title = type == '+' ? p_lang('赠送')+"_"+title+"_#"+uid : p_lang('扣除')+"_"+title+"_#"+uid;
			var lft = type == '+' ? p_lang('赠送') : p_lang('扣除');
			$.dialog({
				'title':title,
				'lock':true,
				'content':lft+'：<input type="text" style="width:70px" id="a_val" /> '+unit+'<br /><br />'+p_lang('说明')+'：<input type="text" id="a_note" value="" style="width:300px" />',
				'ok':function(){
					var url = get_url('wealth','val','wid='+id+'&uid='+uid);
					var note = $("#a_note").val();
					if(!note){
						$.dialog.alert(p_lang('请填写相关说明'));
						return false;
					}
					url += "&note="+$.str.encode(note);
					var val = $("#a_val").val();
					if(!val || (val && parseFloat(val)<=0.01)){
						$.dialog.alert(p_lang('请填写数值，数值必须大于0.01'));
						return false;
					}
					url += "&val="+val;
					url += "&type="+type;
					var rs = $.phpok.json(url);
					if(rs.status == 'ok'){
						$.dialog.alert(p_lang('操作成功'),function(){
							$.phpok.reload();
							return true;
						},'succeed');
					}else{
						$.dialog.alert(rs.content);
						return false;
					}
				},
				'okVal':p_lang('提交'),
				'cancel':true
			});
		},
		rule_help:function()
		{
			top.$.dialog({
				'content':document.getElementById("help_info"),
				'width':'500px',
				'height':'350px',
				'lock':true,
				'cancel':true,
				'cancelVal':'关闭'
			});
		},
		rule_add:function(wid)
		{
			var url = get_url('wealth','rule_set','wid='+wid);
			$.dialog.open(url,{
				'title':p_lang('添加新规则'),
				'width':'700px',
				'height':'700px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'cancel':true,
				'lock':true
			});
		},
		rule_edit:function(id)
		{
			var url = get_url('wealth','rule_set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑规则')+"_#"+id,
				'width':'700px',
				'height':'700px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'lock':true
			});
		},
		rule_delete:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除当前规则吗？'), function () {
				var url = get_url('wealth', 'delete_rule', 'id=' + id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'));
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);