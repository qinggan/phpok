/**
 * 在线升级
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月10日
**/
;(function($){
	$.admin_update = {
		check:function(){
			var url = get_url('update','check');
			$.phpok.json(url,function(data){
				if(data.status == 'ok'){
					$.dialog.alert('系统检测到有更新包，建议您升级');
				}else{
					$.dialog.alert(data.content);
				}
			});
		},
		setting:function(){
			var url = get_url('update','set');
			$.dialog.open(url,{
				'title':p_lang('配置升级环境'),
				'width':'700px',
				'height':'232px',
				'lock':true,
				'cancel':true,
				'okVal':p_lang('提交保存'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_update.save();
					return false;
				}
			});
		},
		save:function(){
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('update','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('升级环境配置成功')).lock();
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);

						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		zip:function(){
			var url = get_url('update','zip');
			$.dialog.open(url,{
				'title':p_lang('ZIP包离线升级'),
				'lock':true,
				'width':'500px',
				'height':'150px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('上传离线包升级'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},
		main:function(){
			var tip = p_lang('在线升级会连接远程服务器，响应较慢，请耐心等候！<br>不使用在线升级，请点“取消”');
			$.dialog.confirm(tip,function(){
				$.phpok.go(get_url('update','main'));
			});
		}
	}
})(jQuery);