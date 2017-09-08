/**
 * 全局参数动作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年07月01日
**/
;(function($){
	$.admin_all = {
		setting_style:function(site_id)
		{
			var tpl_id = $("#tpl_id").val();
			$.dialog.open(get_url('all','tpl_setting','id='+site_id+"&tplid="+tpl_id),{
				'title':p_lang('站点ID {id} 自定义模板设置','<span class="red">#'+site_id+'</span>'),
				'lock':true,
				'id':'phpok_tpl_setting',
				'width':'800px',
				'height':'70%',
				'lock':true,
				'drag':false,
				'button': [{
					name:p_lang('提交保存配置'),
					callback: function () {
						var iframe = this.iframe.contentWindow;
						if (!iframe.document.body) {
							alert('iframe还没加载完毕呢');
							return false;
						};
						iframe.save();
						return false;
					},
					focus:true
				},{
					name:p_lang('初始化模板配置'),
					callback: function () {
						var iframe = this.iframe.contentWindow;
						var url = get_url('all','tpl_resetting','id='+site_id);
						$.phpok.json(url,function(rs){
							if(rs.status){
								$.dialog.alert(p_lang('数据初始化成功'),function(){
									iframe.$.phpok.reload();
								},'succeed');
								return true;
							}
							$.dialog.alert(rs.info);
							return false;
						});
						return false;
					}
				}],
				'cancel':true,'cancelVal':p_lang('关闭')
			})
		}
	}
})(jQuery);