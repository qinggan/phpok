/**
 * 通用桌面组件
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月21日
**/
;(function($){
	$.win = function(title,url,opts){
		top.layui.index.openTabsPage(url, title);
		return true;
	};
	$.win2 = {
		init:function(opts){
			this.opt = opts;
		}
	};
})(jQuery);
