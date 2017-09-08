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
		setting:function(id)
		{
			$.phpok.go(get_url('plugin','setting','id='+id));
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
						html += '<td><img src="images/ico/'+rs.info[i]+'" /></td></tr></table></label></li>';
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
		}
	}
})(jQuery);