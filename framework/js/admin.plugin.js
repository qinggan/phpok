/**
 * 插件管理相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年07月24日
**/
;(function($){
	$.admin_plugin = {
		icon:function(id,vid,title)
		{
			var url = get_url('plugin','icon','id='+id);
			if(vid && vid != 'undefined'){
				url = get_url('plugin','icon','id='+id+'&vid='+vid);
			}
			$.dialog.open(url,{
				'title':title,
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
				},'okVal':p_lang('保存'),'cancel':true
			});
		},
		create:function()
		{
			$.dialog({
				'title':p_lang('创建一个新的插件'),
				'width':'880px',
				'height':'280px',
				'lock':true,
				'content':document.getElementById('create_plugin_html'),
				'ok':function(){
					var url = get_url('plugin','create');
					var title = $("#plugin_name").val();
					if(!title){
						$.dialog.alert(p_lang('插件名称不能为空'));
						return false;
					}
					url += "&title="+$.str.encode(title);
					var id = $("#plugin_id").val();
					if(id){
						url += "&id="+$.str.encode(id);
					}
					var note = $("#plugin_note").val();
					if(note){
						url += "&note="+$.str.encode(note);
					}
					var author = $("#plugin_author").val();
					if(author){
						url += "&author="+$.str.encode(author);
					}
					$.phpok.json(url,function(rs){
						if(rs.status){
							$.dialog.alert(p_lang('插件创建成功，请根据实际情况编写插件扩展'),function(){
								$.phpok.reload();
							},'succeed');
							return true;
						}
						$.dialog.alert(rs.info);
						return false;
					});
				},
				'cancel':true
			});
		},
		install:function(id)
		{
			var url = get_url("plugin","install") + "&id="+$.str.encode(id);
			$.phpok.go(url);
		},
		uninstall:function(id,title)
		{
			$.dialog.confirm(p_lang('确定要卸载插件 {title} 吗？<br>卸载后相应的功能都不能使用','<span class="red">'+title+'</span>'),function(){
				var url = get_url('plugin','uninstall','id='+$.str.encode(id));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('卸载成功…'));
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		config:function(id,title){
			var url = get_url('plugin','extconfig','id='+id);
			$.dialog.open(url,{
				'title':p_lang('设置插件{title}相关参数',' <span class="red">'+title+'</span> '),
				'lock':true,
				'width':'790px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},'cancel':true
			});
		},
		upload:function()
		{
			var url = get_url('plugin','upload');
			$.dialog.open(url,{
				'title':p_lang('上传插件'),
				'width':'500px',
				'height':'150px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('开始上传'),
				'cancel':true
			})
		},
		setting:function(id,index)
		{
			$.win(p_lang('插件管理')+"_#"+index,get_url('plugin','setting','id='+id));
		},
		tozip:function(id)
		{
			var url = get_url('plugin','zip','id='+id);
			$.phpok.go(url);
		},
		einfo:function(val)
		{
			if(!val || val == 'undefined'){
				$("input[name=title]").val('');
				return true;
			}
			var info = $("select[name=efunc]").find('option:selected').attr('data-title');
			$("input[name=title]").val($.trim(info));
			return true;
		},
		showicolist:function(type)
		{
			if(type == 'none' || !type || type == 'undefined'){
				$("#iconlist").hide();
				$("#iconlist_html").html('');
				return true;
			}
			var url = get_url('plugin','iconlist','type='+type);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				var html = '<ul class="layout">';
				for(var i in rs.info){
					if(type == 'menu'){
						html += '<li style="width:180px;"><label title="'+rs.info[i]+'"><table><tr><td><input type="radio" name="icon" value="'+rs.info[i]+'"/></td>';
						html += '<td><i class="icon-'+rs.info[i]+'" style="font-size:16px;"></i> '+rs.info[i]+'</td></tr></table></label></li>';
					}else{
						html += '<li><label title="'+rs.info[i]+'"><table><tr><td><input type="radio" name="icon" value="'+rs.info[i]+'" /></td>';
						html += '<td><img src="images/ico/'+rs.info[i]+'" style="max-width:36px;" /></td></tr></table></label></li>';
					}
				}
				html += '</ul>';
				$("#iconlist_html").html(html);
				$("#iconlist").show();
				return true;
			})
		},
		icon_del:function(id,vid,title)
		{
			var url = get_url('plugin','icon_delete','id='+id+"&vid="+vid);
			$.dialog.confirm(p_lang('确定要删除插件快捷操作项：{title}吗？','<span class="red">'+title+'</span> '),function(){
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		taxis:function(id,old)
		{
			$.dialog.prompt(p_lang('设置要排序的数值，越小越往前靠，最小值为0'),function(val){
				if(!val){
					$.dialog.alert('排序值不能为空');
					return false;
				}
				if(val == old){
					$.dialog.alert('排序值不能和旧的一样');
				}
				var url = get_url('plugin','taxis','id='+$.str.encode(id)+"&taxis="+$.str.encode(val));
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			},old);
		},
		status:function(id)
		{
			var url = get_url('plugin','status','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					if(rs.info == 1){
						$("#status_"+id).removeClass("status0").addClass("status1");
					}else{
						$("#status_"+id).removeClass("status1").addClass("status0");
					}
					$("#status_"+id).val(rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		}
	}
})(jQuery);