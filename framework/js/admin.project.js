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
		 * 项目编辑保存
		**/
		save:function(id)
		{
			$("#"+id).ajaxSubmit({
				'url':get_url('project','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("#id").val() ? p_lang('项目信息编辑成功') : p_lang('项目信息创建成功');
						$.dialog.tips(tip,function(){
							$.admin.reload(get_url('project'));
							$.admin.close();
						}).lock();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 设置样式
		 * @参数 id 要保存的文本框
		 * @参数 vid 要马上看到效果的ID
		**/
		style_setting:function(id,vid)
		{
			if(!id || id == 'undefined'){
				id = 'style';
			}
			if(!vid || vid == 'undefined'){
				vid = 'title';
			}
			var url = get_url('open','style','id='+id+'&vid='+vid);
			$.dialog.open(url,{
				'title':p_lang('样式设置'),
				'width':'550px',
				'height':'270px',
				'lock':true,
				'button':[{
					name: p_lang('保存样式'),
					callback: function () {
						var iframe = this.iframe.contentWindow;
						if (!iframe.document.body) {
							alert('iframe还没加载完毕呢');
							return false;
						};
						iframe.save();
						return false;
					},
					focus: true
				},{
					name: p_lang('清空样式'),
					callback: function () {
						$("#"+id).val('');
						$("#"+vid).removeAttr("style");
						return true;
					},
					focus: false
				}],
				'cancel':true
			});
		},

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
			//清除字段
			//$("#tmp_fields_btn").html('');
			var c = '';
			var f = '';
			if(mtype == 1){
				c += '<input type="button" value="ID" onclick="phpok_admin_orderby(\'orderby2\',\'id\')" class="layui-btn layui-btn-sm" />';
			}
			$.phpok.json(get_url('project','mfields','id='+val),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				if(rs.info){
					var list = rs.info;
					for(var i in list){
						if(list[i].type == 'varchar'){
							if(mtype == 1){
								c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby2\',\''+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
							}else{
								c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby\',\'ext.'+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
							}
						}
						f += '<input type="button" value="'+list[i].title+'" onclick="$.admin_project.fields_add(\''+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
					}
				}
				if(f && f != ''){
					f += '<input type="button" value="'+p_lang('全部')+'" class="layui-btn layui-btn-sm layui-btn-normal" onclick="$.admin_project.fields_add(\'*\')" />';
					f += '<input type="button" value="'+p_lang('不读扩展')+'" class="layui-btn layui-btn-sm layui-btn-danger" onclick="$.admin_project.fields_add(\'id\')" />';
					$("#tmp_fields_btn").html(f).show();
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
		fields_add:function(val)
		{
			if(val == '*' || val == 'id'){
				$("#list_fields").val(val);
				return true;
			}
			var tmp = $("#list_fields").val();
			if(tmp == '*' || tmp == 'id'){
				$("#list_fields").val(val);
				return true;
			}
			var n = tmp;
			if(tmp){
				n += ',';
			}
			n += val;
			$("#list_fields").val(n);
			return true;
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
		extinfo:function(id)
		{
			if(!id || id == 'undefined'){
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
			}
			$.win(p_lang('扩展字段')+"_"+$("#id_"+id).attr("data-title"),get_url('project','content','id='+id));
			return true;
		},
		extinfo_save:function(id)
		{
			$("#"+id).ajaxSubmit({
				'url':get_url('project','content_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('数据保存成功'),function(){
							$.admin.reload(get_url('project'));
						}).lock();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
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
		},
		ext_help:function()
		{
			top.$.dialog({
				'title':p_lang('扩展项帮助说明'),
				'content':document.getElementById('ext_help'),
				'lock':true,
				'width':'700px',
				'height':'500px',
				'padding':'0 10px'
			})
		},
		icolist:function()
		{
			$.dialog.open(get_url('project','icolist'),{
				'title':p_lang('选择图标'),
				'lock':true,
				'width':'700px',
				'height':'60%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交',
				'cancel':true
			})
		}
	};

	$(document).ready(function(){
		if($("#_quick_insert").length>0){
			var module = $("#_quick_insert").attr("data-module");
			var url = get_url('ext','select','type=project&module='+$.str.encode(module));
			$.phpok.ajax(url,function(rs){
				$("#_quick_insert").html(rs);
				layui.form.render();
			});
		}
	});
})(jQuery);

