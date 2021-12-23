/**
 * 后面页面脚本_适用于整个PHPOK5平台的优惠系统
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年01月02日 15时35分
**/
;(function($){
	$.admin_coupon = {
		del:function(id,code)
		{
			var tip = p_lang('确定要删除优惠码{code}吗？未过期的优惠码不建议删除',' <span class="red">'+code+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url('coupon','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		rand:function()
		{
			var str = $.phpok.rand(1,'letter')+''+$.phpok.rand(8,'fixed');
			$("#code").val(str.toUpperCase());
			return true;
		},
		check:function(obj)
		{
			var code = $("#code").val();
			if(!code){
				$.dialog.alert(p_lang('优惠码不能为空'));
				return false;
			}
			var url = get_url('coupon','check','code='+$.str.encode(code));
			var id = $("#id").val();
			if(id && id != 'undefined'){
				url += "&id="+id;
			}
			var t = $.dialog.tips(p_lang('正在检测…')).lock();
			$.phpok.json(url,function(rs){
				t.close();
				if(rs.status){
					$.dialog.alert(p_lang('优惠码可用'),function(){
						return true;
					},'succeed');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		status:function(id)
		{
			if(!id){
				$.dialog.alert(p_lang('操作非法'));
				return false;
			}
			var url = get_url('coupon','status','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					var n_t = rs.info == 1 ? 0 : 1;
					$("#status_"+id).attr("value",rs.info);
					$("#status_"+id).removeClass("status"+n_t).addClass("status"+rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		taxis:function(id,obj)
		{
			var t = $(obj).text();
			$.dialog.prompt(p_lang('请输入新的排序值'),function(val){
				if(!val || val == t){
					return false
				}
				var url = get_url('coupon','taxis','id='+id+"&taxis="+val);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('排序修改成功')).lock();
						$(obj).text(val);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			},t);
		}
	}
})(jQuery);
