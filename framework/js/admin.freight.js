/**
 * 运费
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月27日
**/
;(function($){
	$.admin_freight = {
		update:function(id)
		{
			var url = get_url('freight','set','id='+id);
			$.win(p_lang('编辑运费模板')+"_#"+id,url);
		},
		add:function()
		{
			$.win(p_lang('创建运费模板'),get_url('freight','set'));
		},
		del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('确定要删除该区域：{title} 吗？删除后，已使用此模板相关信息也会删除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('freight','delete','id='+id),function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('删除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		price:function(id)
		{
			$.dialog.open(get_url('freight','price','fid='+id),{
				'title':p_lang('运费价格')+" #"+id,
				'width':'90%',
				'height':'80%',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('保存运费信息'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		vweight:function(obj)
		{
			var o = $(obj).attr("data-value");
			$.dialog.prompt(p_lang('请设置系统体积与重量的换算关系，<a href="//baike.baidu.com/item/%E4%BD%93%E7%A7%AF%E9%87%8D/10675205" target="_blank">关于体积重请点此了解详情</a>'),function(val){
				if(!val || val == ''){
					$.dialog.alert(p_lang('体积重值不能为空'));
					return false;
				}
				if(val == o){
					$.dialog.alert(p_lang('体积重没有变化，不需要修改'));
					return false;
				}
				var url = get_url('freight','vweight','val='+val);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$(obj).attr("data-value",val);
					$.dialog.tips(p_lang('体积重设置成功'));
				});
			},o);
		},
		zone_add:function(fid)
		{
			var url = get_url('freight','zone_setting','fid='+fid);
			$.win(p_lang('添加新区域'),url);
		},
		zone_edit:function(id)
		{
			var url = get_url('freight','zone_setting','id='+id);
			$.win(p_lang('编辑区域')+"_#"+id,url);
		},
		zone_del:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除这块区域配置吗'),function(){
				$.phpok.json(get_url('freight','zone_delete','id='+id),function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return true;
				});
			});
		},
		zone_taxis:function(id,val)
		{
			var url = get_url('freight','zone_sort','id='+id+"&val="+val);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('排序变更成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		}
	}
})(jQuery);