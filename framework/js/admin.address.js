/**
 * 地址库中涉及到的 JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月04日
**/

;(function($){
	$.admin_address = {
		order:function(id,type)
		{
			var opener = $.dialog.opener;
			var url = get_url('address','one','id='+id);
			$.phpok.json(url,function(rs){
				console.log(rs);
				if(rs.status){
					var data = rs.info;
					opener.$("#"+type+"-fullname").val(data.fullname);
					if(data.firstname && data.firstname != 'undefined'){
						opener.$("#"+type+"-firstname").val(data.firstname);
					}
					if(data.lastname && data.lastname != 'undefined'){
						opener.$("#"+type+"-lastname").val(data.lastname);
					}
					opener.$("#"+type+"-country").val(data.country);
					opener.$("#"+type+"-province").val(data.province);
					opener.$("#"+type+"-city").val(data.city);
					opener.$("#"+type+"-county").val(data.county);
					opener.$("#"+type+"-address").val(data.address);
					opener.$("#"+type+"-zipcode").val(data.zipcode);
					opener.$("#"+type+"-email").val(data.email);
					opener.$("#"+type+"-mobile").val(data.mobile);
					opener.$("#"+type+"-tel").val(data.tel);
					$.dialog.close();
				}else{
					$.dialog.alert(rs.info);
				}
			})
		},
		edit:function(id)
		{
			$.dialog.open(get_url('address','set','id='+id),{
				'title':p_lang('编辑地址')+" #"+id,
				'width':'650px',
				'height':'550px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_address.save();
					return false;
				},
				'resize':false,
				'okVal':p_lang('保存地址'),
				'cancel':true
			});
		},
		//添加地址
		add:function()
		{
			$.dialog.open(get_url('address','set'),{
				'title':p_lang('添加地址'),
				'width':'650px',
				'height':'550px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_address.save();
					return false;
				},
				'resize':false,
				'okVal':p_lang('保存地址'),
				'cancel':true
			});
		},
		del:function(id)
		{
			var tip = p_lang('确定要ID为 <span class="red">{id}</span> 的数据吗？<br>删除后地址库信息是不能恢复的',"#"+id);
			layer.confirm(tip,function(index){
				var url = get_url('address','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						layer.msg(p_lang('地址删除成功'));
						$("#address_"+id).remove();
						layer.close(index);
						return true;
					}
					layer.alert(data.info);
					return true;
				});
			});
		},
		save:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('address','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						var tip = id ? p_lang('地址信息编辑成功') : p_lang('地址信息添加成功');
						$.dialog.tips(tip,function(){
							opener.$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);

