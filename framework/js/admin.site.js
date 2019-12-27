/**
 * 站点相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年04月13日
**/
;(function($){
	$.phpok_site = {
		del:function(id,title)
		{
			var tip = "确定要删除网站 {title} 吗？<br>删除后网站相关信息都将删除且不能恢复，请慎用";
			$.dialog.confirm(p_lang(tip,'<span class="red i">'+title+'</span>'),function(){
				//删除网站操作
				var url = get_url("site","delete",'id='+id);
				var tip_obj = $.dialog.tips("正在删除站点信息…",100);
				$.phpok.json(url,function(data){
					$.dialog.close(tip_obj);
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		set_default:function(id,title)
		{
			var tip = "确定要设置网站 {title} 为默认网站吗?";
			$.dialog.confirm(p_lang(tip,"<span class='red i'>"+title+"</span>"),function(){
				$.phpok.json(get_url("site",'default','id='+id),function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		alias:function(id,old)
		{
			if(!old || old == 'undefined'){
				old = '';
			}
			$.dialog.prompt(p_lang('请输入站点别名'),function(val){
				if(!val){
					$.dialog.alert(p_lang('别名不能为空'));
					return false;
				}
				var url = get_url('site','alias','id='+id+'&alias='+$.str.encode(val));
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.alert(p_lang('别名设置成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			},old);
		},
		add:function()
		{
			$.dialog.open(get_url('site','add'),{
				'title': p_lang('添加站点')
				,'lock': true
				,'width': '450px'
				,'height': '150px'
				,'resize': false
				,'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				}
				,'okVal':p_lang('添加新站点')
				,'cancel':true
			});
		}
	}
	$.admin_site = {
		order_edit:function(id)
		{
	        $.dialog.open(get_url('site', 'order_status_set', 'id=' + id), {
	            'title': p_lang('编辑'),
	            'lock': true,
	            'width': '550px',
	            'height': '500px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe还没加载完毕呢');
	                    return false;
	                }
	                iframe.$.admin_site.order_save();
	                return false;
	            },
	            'okVal': p_lang('提交修改'),
	            'cancel': true,
	            'cancelVal':p_lang('取消关闭')
	        })
		},
		order_save:function()
		{
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'order_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('编辑成功',function(){
							obj.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		adm_add_it:function()
		{
			var url = get_url('site', 'admin_status_set');
	        $.dialog.open(url, {
	            'title': p_lang('添加状态'),
	            'lock': true,
	            'width': '450px',
	            'height': '300px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe还没加载完毕呢');
	                    return false;
	                }
	                iframe.$.admin_site.adm_order_save();
	                return false;
	            },
	            'okVal': p_lang('提交保存'),
	            'cancel': true
	        })
		},
		adm_edit_it:function(id)
		{
			var url = get_url('site', 'admin_status_set', "id=" + id);
	        $.dialog.open(url, {
	            'title': p_lang('编辑状态') + " #" + id,
	            'lock': true,
	            'width': '450px',
	            'height': '300px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe还没加载完毕呢');
	                    return false;
	                }
	                iframe.$.admin_site.adm_order_save();
	                return false;
	            },
	            'okVal': p_lang('提交保存'),
	            'cancel': true
	        });
		},
		adm_order_save:function()
		{
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'admin_order_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('数据保存成功',function(){
							obj.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		delete_it:function(id,obj)
		{
			$.dialog.confirm(p_lang('确定要删除该订单状态吗？注意，相应的订单状态不会删除'),function(){
				var url = get_url('site','admin_order_status_delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$(obj).parent().parent().remove();
						$.dialog.tips(p_lang('订单状态删除成功'));
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		edit_price:function(id)
		{
			var url = get_url('site','edit_price','id='+id);
			$.dialog.open(url,{
				'title': p_lang('编辑状态') + " #" + id,
				'lock': true,
				'width':'550px',
				'height':'300px',
				'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe还没加载完毕呢');
	                    return false;
	                }
	                iframe.$.admin_site.edit_price_save();
	                return false;
	            },
	            'okVal': p_lang('提交保存'),
	            'cancel': true
			})
		},
		edit_price_save:function()
		{
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'price_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('数据保存成功',function(){
							obj.$.phpok.reload();
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
	$(document).ready(function(){
		$.getScript('js/clipboard.min.js',function(){
			var clipboard = new Clipboard('.site-url-copy');
			clipboard.on('success', function(e) {
				$.dialog.alert(p_lang('网址复制成功'));
				e.clearSelection();
			});

			clipboard.on('error', function(e) {
				$.dialog.alert(p_lang('网址复制失败'));
			});
		});
	});
})(jQuery);

