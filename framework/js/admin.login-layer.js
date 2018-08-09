/**
 * 管理员登录页
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月05日
**/

;(function($){
	$.admin_login = {
		code:function(imgid)
		{
			var url = api_url('vcode');
			$("#"+imgid).attr("src",$.phpok.nocache(url));
		},
		language:function(val)
		{
			var url = get_url('login','','_langid='+val);
			$.phpok.go(url);
		},
		ok:function()
		{
			var self = this;
			$("#post_save").ajaxSubmit({
				'url':get_url('login','ok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						layer.msg(p_lang('登入成功'), {
		  					icon: 1,
		  					time: 1000
		  				}, function() {
		  					$.phpok.go(get_url('index'));
		  				});
						return true;
					}
					layer.msg(rs.info,{
						icon:2,time:1000
					},function(){
						$("input[name=user],input[name=pass],input[name=_code]").val('');
						self.code('src_code');
					});
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);


$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
	if($('input[name=_code]').length > 0){
		$.admin_login.code('src_code');
		$("#src_code").click(function(){
			$.admin_login.code('src_code');
		})
	}

	layui.config({
	  	base: webroot+'static/admin/' //静态资源所在路径
	}).extend({
	  	index: 'lib/index' //主入口模块
	}).use(['index', 'user', 'form'], function() {
		setter = layui.setter,
		admin = layui.admin,
		form = layui.form,
		router = layui.router(),
		search = router.search;
	  	form.render();
	});

});