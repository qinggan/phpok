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
			var url = get_url('freight','save','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var type = $("#type_"+id).val();
			if(type){
				url += "&type="+type;
			}
			var currency_id = $("#currency_"+id).val();
			if(currency_id){
				url += "&currency_id="+currency_id;
			}
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('运费模板修改成功')).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		add:function()
		{
			var url = get_url('freight','save');
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert('名称不能为空');
				return false
			}
			url += "&title="+$.str.encode(title);
			var type = $("#type_0").val();
			if(type){
				url += "&type="+type;
			}
			var currency_id = $("#currency_0").val();
			if(currency_id){
				url += "&currency_id="+currency_id;
			}
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('运费模板修改成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
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
		zone_add:function(fid)
		{
			var url = get_url('freight','zone_setting','fid='+fid);
			$.dialog.open(url,{
				'title':p_lang('添加新区域'),
				'lock':true,
				'width':'700px',
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
				'okVal':p_lang('保存添加'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		zone_edit:function(id)
		{
			var url = get_url('freight','zone_setting','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑区域')+"_#"+id,
				'lock':true,
				'width':'700px',
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
				'okVal':p_lang('保存修改'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
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