/**
 * SQL操作类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_sql = {

		/**
		 * 选择只有碎片的表
		**/
		select_free:function()
		{
			$.input.checkbox_none();
			$("input[sign='free']").prop("checked",true);
			return true;
		},

		/**
		 * 优化数据表
		**/
		optimize:function()
		{
			var id = $.input.checkbox_join();
			if(!id){
				$.dialog.alert(p_lang('请选择数据表'));
				return false;
			}
			var url = get_url('sql','optimize','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.alert(p_lang('数据优化成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},

		/**
		 * 修复数据表
		**/
		repair:function()
		{
			var id = $.input.checkbox_join();
			if(!id){
				$.dialog.alert(p_lang('请选择数据表'));
				return false;
			}
			var url = get_url('sql','repair','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.alert(p_lang('数据表修复成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},

		/**
		 * 备份数据表
		**/
		backup:function()
		{
			$.dialog.confirm(p_lang('确定要执行备份操作吗？未选定表将备份全部！'),function(){
				var id = $.input.checkbox_join();
				if(!id){
					id = 'all';
				}
				var url = get_url('sql','backup','id='+$.str.encode(id));
				$.phpok.go(url);
			});
		},

		/**
		 * 恢复指定的备份文件
		**/
		recover:function(id)
		{
			$.dialog.confirm(p_lang('确定要恢复到这个备份'),function(){
				var url = get_url('sql','recover','id='+id);
				$.phpok.go(url);
			});
		},

		/**
		 * 删除指定的备份文件
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这个备份吗？删除后就不能恢复了'),function(){
				var url = get_url('sql','delete','id='+id);
				$.phpok.go(url);
			});
		},

		/**
		 * 查看表明细信息
		**/
		show:function(tbl)
		{
			var url = get_url('sql','show','table='+$.str.encode(tbl));
			$.dialog.open(url,{
				'title':p_lang('查看表 {tbl} 明细',tbl),
				'lock':true,
				'width':'750px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('文本替换'),
				'cancel':true
			});
		},

		/**
		 * 删除表操作
		**/
		tbl_delete:function(tbl)
		{
			$.dialog.confirm(p_lang('确定要删除表 {tbl} 信息吗？',tbl),function(){
				var url = get_url('sql','table_delete','tbl='+$.str.encode(tbl));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('删除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);