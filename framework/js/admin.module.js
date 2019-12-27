/**
 * 模块管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_module = {

		/**
		 * 字段删除
		**/
		field_del:function(id,title)
		{
			var tip = p_lang('确定要删除字段：{title}？<br/>删除此字段将同时删除相应的内容信息','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url("module","field_delete") + "&id="+id;
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
		 * 字段编辑
		**/
		field_edit:function(id)
		{
			var url = get_url("module","field_edit") + "&id="+id;
			$.dialog.open(url,{
				'title':p_lang('编辑字段 #{id}',id),
				'lock':true,
				'width':'600px',
				'height':'70%',
				'resize':false,
				'drag':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('保存编辑信息'),
				'cancel':function(){
					return true;
				}
			});
		},

		/**
		 * 字段快速添加
		**/
		field_add:function(id,fid)
		{
			var url = get_url("module","field_add",'id='+id+'&fid='+fid);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.phpok.reload();
					return false;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},

		/**
		 * 字段标准添加
		**/
		field_addok:function(mid)
		{
			var obj = art.dialog.opener;
			$("#form_save").ajaxSubmit({
				'url':get_url('module','field_addok','mid='+mid),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('字段创建成功'),function(){
							obj.$.dialog.close();
							obj.$.phpok.reload();
						});
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 添加模块字段弹出操作
		**/
		field_create:function(id)
		{
			$.dialog.open(get_url("module","field_create","mid="+id),{
				'title':p_lang('添加字段'),
				'lock':true,
				'width':'650px',
				'height':'70%',
				'resize':false,
				'drag':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('提交保存'),
				'cancel':true
			})
		},

		/**
		 * 保存创建的模块
		**/
		set_save:function()
		{
			var obj = art.dialog.opener;
			$("#module_submit_post").ajaxSubmit({
				'url':get_url('module','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						var tip = !id ? p_lang('模块添加成功') : p_lang('模块编辑成功');
						$.dialog.alert(tip,function(){
							obj.$.phpok.reload();
						},'succeed');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 模块创建
		**/
		create:function()
		{
			$.dialog.open(get_url('module','set'),{
				'title':p_lang('模块添加'),
				'lock':true,
				'width':'650px',
				'height':'368px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.$.admin_module.set_save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancelVal':p_lang('取消'),
				'cancel':true,
				'resize':true
			});
		},

		/**
		 * 模块导入
		**/
		input:function()
		{
			$.dialog.open(get_url('module','import'),{
				'title':p_lang('模块导入'),
				'lock':true,
				'width':'500px',
				'height':'150px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('导入模块'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		/**
		 * 模块编辑
		**/
		edit:function(id)
		{
			$.dialog.open(get_url('module','set','id='+id),{
				'title':p_lang('模块修改 #{id}',id),
				'lock':true,
				'width':'650px',
				'height':'400px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.$.admin_module.set_save();
					//iframe.save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		/**
		 * 模块删除
		**/
		del:function(id,title)
		{
			$.dialog.confirm(p_lang('确定要删除模块：{title}？<br/>如果模块中有内容，也会相应的被删除，请慎用','<span style="color:red;font-weight:bold;">'+title+'</span>'),function(){
				var url = get_url("module","delete")+"&id="+id;
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},

		/**
		 * 模块导出
		**/
		export:function(id)
		{
			var url = get_url('module','export','id='+id);
			$.phpok.go(url);
		},

		/**
		 * 自定义模块要显示的字段
		**/
		layout:function(id,title)
		{
			$.dialog.open(get_url("module","layout","id="+id),{
				"title":p_lang('模型：{title} 后台列表布局','<span class="red">'+title+'</span>'),
				"width":"700px",
				"height":"400px",
				"win_min":false,
				"win_max":false,
				"resize": false,
				"lock": true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.$.admin_module.layout_save();
					//iframe.save();
					return false;
				},
				'okVal':p_lang('保存'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		layout_save:function()
		{
			var obj = art.dialog.opener;
			$("#module_layout_save").ajaxSubmit({
				'url':get_url('module','layout_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.close();
						$.dialog.tips('配置成功');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 模块复制
		**/
		copy:function(id,title)
		{
			$.dialog.prompt(p_lang('请设置新模块的名称：'),function(val){
				if(!val){
					$.dialog.alert(p_lang('名称不能为空'));
					return false;
				}
				var url = get_url("module","copy","id="+id+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			},title);
		},

		/**
		 * 模块状态变更
		**/
		status:function(id)
		{
			$.phpok.json(get_url("module","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		/**
		 * 模块排序
		**/
		taxis:function(id,taxis)
		{
			$.dialog.prompt(p_lang('请填写新的排序：'),function(val){
				if(val != taxis){
					$.phpok.json(get_url('module','taxis','taxis='+val+"&id="+id),function(rs){
						if(rs.status){
							$.phpok.reload();
							return true;
						}
						$.dialog.alert(rs.info);
						return false;
					});
				}
			},taxis);
		}
	}
})(jQuery);