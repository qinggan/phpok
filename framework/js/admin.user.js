/**
 * 后台会员涉及到的地址
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年05月27日
**/
;(function($){
	$.admin_user = {
		address:function(id)
		{
			var url = get_url('address','open','type=user_id&keywords='+id);
			$.dialog.open(url,{
				'title':p_lang('会员地址'),
				'width':'800px',
				'height':'500px',
				'lock':true
			})
		},
		show_setting:function()
		{
			var url = get_url('user','show_setting');
			$.dialog.open(url,{
				'title':p_lang('会员字段显示设置'),
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
		}
	}
})(jQuery);