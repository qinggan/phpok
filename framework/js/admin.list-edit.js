/**
 * 内容管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年01月16日
**/
var autosave_handle;

;(function($){
	var count_down;
	$.admin_list_edit = {

		autosave_checkbox:function(obj)
		{
			var that = this;
			if($(obj).is(":checked")){
				$(obj).parent().find('span').html(p_lang('已功能自动保存功能，当前剩余{seconds}秒',' <b style="color:red;">300</b> '));
				count_down = window.setTimeout(function(){
					that.autosave_countdown(obj);
				}, 1000);
			}else{
				$(obj).parent().find('span').html(p_lang('开启自动保存功能'));
				window.clearTimeout(count_down);
			}
		},

		autosave_countdown:function(obj)
		{
			window.clearTimeout(count_down);
			var that = this;
			var num = $(obj).parent().find("b").text();
			if(!num || num == 'undefined'){
				return false;
			}
			num = parseInt(num);
			if(num < 1){
				this.autosave(obj);
				return true;
			}
			num--;
			$(obj).parent().find("b").text(num);
			window.setTimeout(function(){
				that.autosave_countdown(obj);
			}, 1000);
		},

		/**
		 * 自动保存
		**/
		autosave:function(obj)
		{
			window.clearTimeout(count_down);
			var that = this;
			var id = $("#id").val();
			//忽略标题
			var title = $("#title").val();
			if(!title){
				var tmp = {};
				tmp.seconds = ' <b style="color:red;">300</b> ';
				$(obj).parent().find('span').html(p_lang('标题未填写，跳过自动保存，距下一次自动保存剩余{seconds}秒',tmp));
				count_down = window.setTimeout(function(){
					that.autosave_countdown(obj);
				}, 1000);
				return true;
			}
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','ok','_autosave=1'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status != 'ok'){
						$.dialog.alert(p_lang('自动保存失败，原因：{info}',rs.content));
						$(obj).parent().find('span').html(p_lang('重新开始计算自动保存，当前剩余{seconds}秒',' <b style="color:red;">300</b> '));
						count_down = window.setTimeout(function(){
							that.autosave_countdown(obj);
						}, 1000);
						return false;
					}
					//将增加变成修改
					if(!id || id == 'undefined' && id == '0'){
						$("#id").val(rs.content);
					}
					var tmp = {};
					tmp.date = ' <i class="darkblue">'+(new Date()).toString()+'</i> ';
					tmp.seconds = ' <b style="color:red;">300</b> ';
					$(obj).parent().find('span').html(p_lang('数据于{date}自动保存，距下一次自动保存剩余{seconds}秒',tmp));
					count_down = window.setTimeout(function(){
						that.autosave_countdown(obj);
					}, 1000);
					return true;
				}
			});
		},

		/**
		 * 保存数据
		**/
		save:function()
		{
			var loading_action;
			var id = $("#id").val();
			var pcate = $("#_root_cate").val();
			var pcate_multiple = $("#_root_cate_multiple").val();
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','ok'),
				'type':'post',
				'dataType':'json',
				'beforeSubmit':function(){
					loading_action = $.dialog.tips('<img src="images/loading.gif" border="0" align="absmiddle" /> '+p_lang('正在保存数据，请稍候…')).time(30).lock();
				},
				'success':function(rs){
					if(loading_action){
						loading_action.close();
					}
					if(rs.status == 'ok'){
						var url = get_url('list','action','id='+$("#pid").val());
						if(pcate>0){
							var cateid = $("#cate_id").val();
							url += "&keywords[cateid]="+cateid;
						}
						if(id){
							$.dialog.alert(p_lang('内容信息修改成功'),function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.admin.close(url);
							},'succeed');
							return true;
						}
						$.dialog.through({
							'icon':'succeed',
							'content':p_lang('内容添加操作成功，请选择继续添加或返回列表'),
							'ok':function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.phpok.reload();
							},
							'okVal':p_lang('继续添加'),
							'cancel':function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.admin.close(url);
							},
							'cancelVal':p_lang('关闭窗口'),
							'lock':true
						});
						return true;

					}
					$.dialog.alert(rs.content);
					return true;
				}
			});
			return false;
		},

		/**
		 * 添加属性
		**/
		attr_create:function()
		{
			var self = this;
			$.dialog.prompt(p_lang('请添加属性名称，注意，添加前请先检查之前的属性是否存在'),function(name){
				if(!name){
					$.dialog.alert(p_lang('名称不能为空'));
				}
				var url = get_url('options','save','title='+$.str.encode(name));
				$.phpok.json(url,function(data){
					if(data.status){
						self.attrlist_load();
						self.attr_add(data.info);
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},

		/**
		 * 选择添加属性
		**/
		attr_add:function(val)
		{
			if(!val){
				return false;
			}
			var old = $("#_biz_attr").val();
			if(old){
				if(old == val){
					$.dialog.alert(p_lang('属性已经使用，不能重复'));
					return false;
				}
				var list = old.split(",");
				var is_used = false;
				for(var i in list){
					if(list[i] == val){
						is_used = true;
						break;
					}
				}
				if(is_used){
					$.dialog.alert(p_lang('属性已经使用，不能重复'));
					return false;
				}
				var ncontent = old+","+val;
				//写入新值
				$("#_biz_attr").val(ncontent);
				//创建HTML
				var html = '<li id="_biz_attr_'+val+'"><li>';
				$("#biz_attr_options").append(html);
				//异步加载HTML
			}else{
				$("#_biz_attr").val(val);
				var html = '<li id="_biz_attr_'+val+'"><li>';
				$("#biz_attr_options").html(html);
			}
			this.attr_info_product(val);
		},

		/**
		 * 删除属性
		 * @参数 id
		**/
		attr_remove:function(val)
		{
			if(!val){
				return false;
			}
			var old = $("#_biz_attr").val();
			if(!old || old == 'undefined' || old == '0'){
				return false;
			}
			if(old == val){
				$("#_biz_attr").val('');
				$("#biz_attr_options").html('');
				return false;
			}
			var list = old.split(",");
			var nlist = new Array();
			var m = 0;
			for(var i in list){
				if(list[i] != val){
					nlist[m] = list[i];
					m++;
				}
			}
			var ncontent = nlist.join(",");
			$("#_biz_attr").val(ncontent);
			//删除HTML
			var html = '<li id="_biz_attr_'+val+'"><li>';
			$("#_biz_attr_"+val).remove();
		},

		/**
		 * 异步加载属性
		**/
		attr_load:function()
		{
			var bizinfo = $("#_biz_attr").val();
			if(bizinfo && bizinfo != 'undefined' && bizinfo != '0'){
				var list = bizinfo.split(",");
				var html = '';
				for(var i in list){
					html += '<li id="_biz_attr_'+list[i]+'"><li>';
				}
				$("#biz_attr_options").html(html);
				for(var i in list){
					this.attr_info_product(list[i]);
				}
			}
		},

		/**
		 * 读取属性及内容信息
		 * @参数 id 属性ID
		**/
		attr_info_product:function(id)
		{
			//执行属性添加
			var url = get_url('list','attr','aid='+id);
			var tid = $("#id").val();
			if(tid){
				url += "&tid="+tid;
			}
			$.phpok.json(url,function(data){
				if(data.status){
					$("#_biz_attr_"+id).html(data.info);
					layui.form.render();
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
		},

		/**
		 * 选择全部属性
		**/
		attrlist_load:function()
		{
			var url = get_url('options','all');
			$.phpok.json(url,function(data){
				if(data.status != 'ok'){
					var html = '<option value="">'+data.content+'</option>';
					$("#biz_attr_id").html(html);
					return false;
				}
				var html = '<option value="">'+p_lang('请选择一个属性…')+'</option>';
				for(var i in data.content){
					html += '<option value="'+data.content[i].id+'">'+data.content[i].title+'</option>';
				}
				$("#biz_attr_id").html(html);
				return true;
			});
			return false;
		},

		attr_option_delete:function(id,val)
		{
			var name = $("#attr_"+id+"_"+val).attr("data-name");
			$("#attr_"+id+"_opt").append('<option value="'+val+'">'+name+'</option>');
			$("#attr_"+id+"_"+val).remove();
		},

		/**
		 * 属性快速添加
		 * @参数 id 属性ID
		 * @参数 val 要写入的值
		**/
		attr_option_quickadd:function(id,val)
		{
			if(!id || !val || val == 'undefined' || val == '0' || val == ''){
				return false;
			}
			var text = $("#attr_"+id+"_opt").find("option:selected").text();
			$("#attr_"+id+"_opt option[value="+val+"]").remove();
			this.attr_option_html(id,val,text);
		},

		/**
		 * 输出HTML
		 * @参数 id 属性ID
		 * @参数 val 参数ID
		 * @参数 text 显示名称
		**/
		attr_option_html:function(id,val,text)
		{
			var count = $("tr[name=attr_"+id+"]").length;
			var taxis = count > 0 ? parseInt(count+1) * 5 : 5;
			var html = '<tr name="attr_'+id+'" id="attr_'+id+'_'+val+'" data-name="'+text+'">';
			html += '<td class="center"><input type="hidden" name="_attr_'+id+'[]" value="'+val+'" />'+text+'</td>';
			html += '<td class="center"><input type="text" name="_attr_weight_'+id+'['+val+']" value="0" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_volume_'+id+'['+val+']" value="0" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_price_'+id+'['+val+']" value="" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_taxis_'+id+'['+val+']" value="'+taxis+'" class="layui-input" /></td>'
			html += '<td class="center"><input type="button" value="'+p_lang('删除')+'" onclick="$.admin_list_edit.attr_option_delete(\''+id+'\',\''+val+'\')" class="layui-btn layui-btn-sm" /></td>';
			html += '</tr>';
			if($("tr[name=attr_"+id+"]").length > 0){
				$("tr[name=attr_"+id+"]").last().after(html);
			}else{
				$("tr[name=attr_"+id+"_thead]").after(html);
			}
			return true;
		},

		/**
		 * 手动添加值信息
		 * @参数 id 属性ID
		**/
		attr_option_add:function(id)
		{
			var self = this;
			$.dialog.prompt(p_lang('请创建一个新值'),function(name){
				if(!name){
					$.dialog.alert(p_lang('新值不能为空'));
					return false;
				}
				var url = get_url('options','save_values','aid='+id+'&title='+$.str.encode(name));
				$.phpok.json(url,function(data){
					if(data.status){
						self.attr_option_html(id,data.info,name);
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		}
	}

})(jQuery);
$(document).keypress(function(e){
	//按钮CTRL+回车键执行保存
	if(e.ctrlKey && e.which == 13 || e.which == 10) {
		$('.phpok_submit_click').click();
	}

});
$(document).ready(function(){


	//仅在添加主题时执行自动保存操作
	/*var id = $("#id").val();
	if(!id || id == '0' || id == 'undefined'){
		autosave_handle = window.setTimeout(function(){
			$.admin_list_edit.autosave();
		}, 60000);
	}*/


	//加载产品属性
	if($("#_biz_attr").length > 0){
		$.admin_list_edit.attr_load();
	}


});