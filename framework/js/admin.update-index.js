/**
 * 在线升级页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月11日
**/
function zip_update()
{
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
		'cancel':function(){return true;}
	});
}
function check_it()
{
	var url = get_url('update','check');
	$.phpok.json(url,function(data){
		if(data.status == 'ok'){
			$.dialog.alert('系统检测到有更新包，建议您升级');
		}else{
			$.dialog.alert(data.content);
		}
	})
}

$(document).ready(function(){
	$("#project li").each(function(i){
		$(this).click(function(){
			var tips = $(this).attr('tips');
			var url = $(this).attr('href');
			var func = $(this).attr('func');
			if(url){
				if(tips){
					$.dialog.confirm(tips,function(){
						$.phpok.go(url);
					})
				}else{
					$.phpok.go(url);
				}
			}else{
				if(func){
					eval(func);
				}
			}
		});
	});
});