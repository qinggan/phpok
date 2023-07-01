/**
 * 后台搜索相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2020年7月21日
**/
;(function($){
	$.admin_search = {
		add:function()
		{
			$.dialog.prompt(p_lang('请填写要增加的关键字，多个关键字用英文逗号隔开'),function(val){
				if(!val || val == ''){
					$.dialog.alert(p_lang('关键字不能为空'));
					return false;
				}
				var url = get_url('search','add','content='+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('关键字添加成功'),function(){
						$.phpok.reload();
					}).lock();
				})
			});
		},
		edit:function(id)
		{
			var url = get_url('search','edit','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑')+"_#"+id,
				'width':'500px',
				'height':"160px",
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_search.save();
					return false;
				},
				'cancel':true
			});
		},
		del:function(id)
		{
			if(!id || id == 'undefined'){
				id = $.checkbox.join();
				if(!id || id == 'undefined'){
					$.dialog.alert(p_lang('请选择要删除的关键字'));
					return false;
				}
			}
			$.dialog.confirm(p_lang('确定要删除 ID 为 #{id} 的关键字吗？',id),function(){
				var url = get_url('search','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('删除成功')).lock();
					$.phpok.reload();
				});
			});
		},
		save:function(){
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('search','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('操作成功'),function(){
							opener.$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		}
	}
})(jQuery);