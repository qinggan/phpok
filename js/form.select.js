/**
 * 下拉操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年08月04日
**/
;(function($){
	$.phpok_form_select = {

		/**
		 * 下拉菜单变化后执行的JS
		 * @参数 groupid 选项组ID
		 * @参数 identifier 变量标识
		 * @参数 val 选中的值
		**/
		change:function(groupid,identifier,val)
		{
			var url = api_url('opt','index','group_id='+groupid+"&identifier="+identifier+"&val="+$.str.encode(val));
			$.phpok.ajax(url,function(data){
				$("#"+identifier+"_html").html(data);
			})
		}
	}
})(jQuery);