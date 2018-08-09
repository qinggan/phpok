/**
 * 项目管理相关JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年10月07日
**/
;(function($){
	$.admin_project = {

		/**
		 * 模块选择时执行触发
		**/
		module_change:function(obj)
		{
			$("#module_set,#module_set2").hide();
			var val = $(obj).val();
			var mtype = $(obj).find('option:selected').attr('data-mtype');
			if(!val || val == '0'){
				return true;
			}
			$("#tmp_orderby_btn,#tmp_orderby_btn2").html('');
			var c = '';
			if(mtype == 1){
				c += '<input type="button" value="ID" onclick="phpok_admin_orderby(\'orderby2\',\'id\')" class="phpok-btn" />';
			}
			$.phpok.json(get_url('project','mfields','id='+val),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				if(rs.info){
					var list = rs.info;
					for(var i in list){
						if(mtype == 1){
							c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby2\',\''+list[i].identifier+'\')" class="phpok-btn"/>';
						}else{
							c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby\',\'ext.'+list[i].identifier+'\')" class="phpok-btn"/>';
						}
					}
				}
				if(mtype == 1){
					$("#tmp_orderby_btn2").html(c);
					$("#module_set2").show();
				}else{
					$("#tmp_orderby_btn").html(c);
					$("#module_set").show();
				}
				return true;
			});
		},
		del:function(id)
		{
			var tip = p_lang('确定要删除此项目吗？删除会将相关内容一起删除 #{id}','<span class="red">'+id);
			$.dialog.confirm(tip,function(){
				var url = get_url('project','delete','id='+id);
				var tips = $.dialog.tips(p_lang('正在执行删除请求…'));
				$.phpok.json(url,function(data){
					tips.close();
					if(data.status){
						$("#project_"+id).remove();
						$.dialog.tips(p_lang('模块删除成功'));
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		copy:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未选择要复制的项目'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('复制操作只能选择一个'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要复制此项目 #{id}','<span class="red">'+id+'</id>'),function(){
				var url = get_url('project','copy','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('项目复制成功'),function(){
							$.phpok.reload();
						})
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		extinfo:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未选择要自定义扩展字段的项目'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('自定义扩展字段操作只能选择一个'));
				return false;
			}
			$.phpok.go(get_url('project','content','id='+id));
			return true;
		},
		export:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未选择要自定义扩展字段的项目'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('自定义扩展字段操作只能选择一个'));
				return false;
			}
			$.phpok.go(get_url('project','export','id='+id));
			return true;
		},
		import_xml:function()
		{
			var url = get_url('project','import');
			$.dialog.open(url,{
				'title':p_lang('项目导入'),
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
				'okVal':p_lang('导入项目'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},
		set_hidden:function(hidden)
		{
			var id = $.checkbox.join();
			if(!id){
				var tip = hidden == 1 ? p_lang('未选择要隐藏的项目') : p_lang('未选择要显示的项目');
				$.dialog.alert(tip);
				return false;
			}
			var tip = hidden == 1 ? p_lang('指定项目已经设为隐藏') : p_lang('指定项目已经设为显示');
			var url = get_url('project','hidden','id='+$.str.encode(id)+"&hidden="+hidden);
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(tip,function(){
						$.phpok.reload();
					});
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		set_lock:function(status)
		{
			var id = $.checkbox.join();
			if(!id){
				var tip = status == 1 ? p_lang('未选择要启用的项目') : p_lang('未选择要禁用的项目');
				$.dialog.alert(tip);
				return false;
			}
			var tip = status == 1 ? p_lang('指定项目已经设为启用') : p_lang('指定项目已经设为禁用');
			var url = get_url('project','status','id='+$.str.encode(id)+"&status="+status);
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(tip,function(){
						$.phpok.reload();
					});
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		set_status:function(id)
		{
			var url = get_url('project','status','id='+id);
			var old_value = $("#status_"+id).attr("value");
			var new_value = old_value == "1" ? "0" : "1";
			url += "&status="+new_value;
			$.phpok.json(url,function(rs){
				if(rs.status){
					$("#status_"+id).removeClass("status"+old_value).addClass("status"+new_value).attr("value",new_value);
					return true;
				}
				$.dialog.alert(rs.info);
			});
		},
		sort:function(val,id)
		{
			var url = get_url('project','sort','sort['+id+']='+val);
			$.phpok.json(url,function(data){
				if(data.status){
					$("div[name=taxis][data="+id+"]").text(val);
					$.dialog.tips(p_lang('排序编辑成功，您可以手动刷新看新的排序效果'));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		}
	}
})(jQuery);

