/**
 * GD操作中涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_gd = {

		/**
		 * 设置编辑器使用哪个图片规格方案
		 * @参数 id 方案ID
		**/
		editor:function(id)
		{
			var url = get_url('gd','editor');
			if(id && id != 'undefined'){
				url += "&id="+id;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},

		/**
		 * 设置编辑器使用原图
		**/
		tofile:function()
		{
			var self = this;
			$.dialog.confirm(p_lang('确定要让编辑器调用原图吗？'),function(){
				self.editor(0);
			});
		},

		/**
		 * 删除配置
		 * @参数 id 要删除的项目ID
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这个图片方案吗？'),function(rs){
				var url = get_url('gd','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},

		/**
		 * 保存方案数据
		**/
		save:function()
		{
			$("#gdsetting").ajaxSubmit({
				'url':get_url('gd','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var info = $("#id").val() ? p_lang('方案编辑成功') : p_lang('方案添加成功');
						$.dialog.tips(info);
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