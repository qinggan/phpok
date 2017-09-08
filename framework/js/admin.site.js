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
			$.dialog.prompt('请输入站点别名：',function(val){
				if(!val){
					$.dialog.alert('别名不能为空');
					return false;
				}
				var url = get_url('site','alias','id='+id+'&alias='+$.str.encode(val));
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.alert('别名设置成功',function(){
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
})(jQuery);