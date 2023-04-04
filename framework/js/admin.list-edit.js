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

		save_not_close:function(obj)
		{
			return this.save(false);
		},

		save2add:function(obj)
		{
			return this.save(2);
		},

		save2close:function(obj)
		{
			return this.save(true);
		},

		save_open:function()
		{
			var opener = $.dialog.opener;
			var loading_action;
			var id = $("#id").val();
			var pcate = $("#_root_cate").val();
			var pcate_multiple = $("#_root_cate_multiple").val();
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','ok'),
				'type':'post',
				'dataType':'json',
				'beforeSubmit':function(){
					loading_action = $.dialog.tips('<img src="images/loading.gif" border="0" align="absmiddle" /> '+p_lang('正在保存数据，请稍候…')).time(3000).lock();
				},
				'success':function(rs){
					if(loading_action){
						loading_action.close();
					}
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					var url = get_url('list','action','id='+$("#pid").val());
					if(pcate>0){
						var cateid = $("#cate_id").val();
						url += "&keywords[cateid]="+cateid;
					}
					if(id){
						$.dialog.tips(p_lang('内容信息修改成功'));
						opener.$.phpok.reload();
						return true;
					}
					$.dialog.tips(p_lang('内容信息添加成功'));
					opener.$.phpok.reload();
					return true;
				}
			});
			return false;
		},

		/**
		 * 保存数据
		**/
		save:function(close,isadd)
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
					loading_action = $.dialog.tips(p_lang('正在保存数据，请稍候…')).time(3000).lock();
				},
				'success':function(rs){
					if(!rs.status){
						loading_action.content(rs.info).time(1.5);
						return false;
					}
					var url = get_url('list','action','id='+$("#pid").val());
					if(pcate>0){
						var cateid = $("#cate_id").val();
						url += "&keywords[cateid]="+cateid;
					}
					if(id){
						$.admin.reload(url);
						if(close){
							loading_action.setting('close',function(){
								$.admin.close(url);
							})
						}
						loading_action.content(p_lang('内容信息修改成功')).time(2);
						return true;
					}
					loading_action.content(p_lang('内容信息添加成功')).time(2);
					if(close == 2){
						$.admin.reload(url);
						var add_url = get_url('list','edit','pid='+$("#pid").val());
						if(pcate>0){
							var cateid = $("#cate_id").val();
							add_url += "&cateid="+cateid;
						}
						$.phpok.go(add_url);
						return true;
					}
					if(close == true){
						$.admin.close(url);
						return true;
					}
					$.admin.reload(url);
					var old_title = $.admin.title();
					var tmp = old_title.split("_");
					$.admin.title(tmp[0]+"_编辑_#"+rs.info);
					$.phpok.go(get_url('list','edit','id='+rs.info));
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
			$("#biz_attr_id").val('');//复位
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
				this.attr_info_product(ncontent);
				return true;
			}
			$("#_biz_attr").val(val);
			this.attr_info_product(val);
			return true;
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
			if(!old || old == 'undefined' || old == '0' || old == val){
				$("#_biz_attr").val('');
				$("#_biz_attr_value").val('');
				$("#biz_attr_options").html('');
				return false;
			}
			var list = old.split(",");
			var nlist = new Array();
			for(var i in list){
				if(list[i] != val){
					nlist.push(list[i]);
				}
			}
			$("#_biz_attr").val(nlist.join(","));
			var vals = $("#_biz_attr_value").val();
			if(vals && vals != ''){
				var t = vals.split(",");
				var l = new Array();
				for(var i in t){
					var tmp = (t[i]).split("_");
					if(tmp[0] != val){
						l.push(t[i]);
					}
				}
				$("#_biz_attr_value").val(l.join(","));
			}
			this.attr_info_product();
		},

		/**
		 * 
		 * @参数 
		 * @参数 
		**/
		attr_delete:function(obj)
		{
			$(obj).parent().parent().remove();
		},

		/**
		 * 读取属性及内容信息
		 * @参数 id 属性ID
		**/
		attr_info_product:function()
		{
			var id = $("#_biz_attr").val();
			if(!id){
				return false;
			}
			var url = get_url('list','attr','aid='+id);
			var vals = $("#_biz_attr_value").val();
			if(vals){
				url += "&vals="+vals;
			}
			var tid = $("#id").val();
			if(tid){
				url += "&tid="+tid;
			}
			$.phpok.json(url,function(data){
				if(data.status){
					$("#biz_attr_options").html(data.info);
					layui.form.render();
					return true;
				}
				$.dialog.tips(data.info);
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
			$("#attr_"+id+"_opt").val('');
			var chk = id+"_"+val;
			var old = $("#_biz_attr_value").val();
			if(!old || old == '' || old == 'undefined'){
				$("#_biz_attr_value").val(chk);
				this.attr_info_product();
				return true;
			}
			var list = old.split(",");
			var is_append = true;
			for(var i in list){
				if(list[i] == chk){
					is_append = false;
					break;
				}
			}
			if(!is_append){
				$.dialog.tips("值已存在");
				return false;
			}
			var n = old+","+chk;
			$("#_biz_attr_value").val(n);
			this.attr_info_product();
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
						self.attr_option_quickadd(id,data.info);
						return true;
					}
					$.dialog.tips(data.info);
					return false;
				});
			});
		},

		country_add:function()
		{
			var obj = $("#country_id");
			var c = obj.val();
			if(!c){
				$.dialog.alert(p_lang('当前国家或组织不能为空'));
				return false;
			}
			if($("#country_"+c).length>0){
				$.dialog.alert(p_lang('国家已使用，请选择其他的'));
				return false;
			}
			var currency = obj.find('option:selected').attr("data-name");
			if(!currency){
				currency = p_lang('默认');
			}
			var rs = {};
			rs['id'] = 0;
			rs['currency_title'] = currency;
			rs['country_id'] = c;
			rs['name'] = obj.find('option:selected').attr("data-country");
			var html = template("world_location_price_tpl", {'rs':rs});
			$("#world_location_price").append(html);
		},
		country_price_delete:function(country_id)
		{
			$("#country_"+country_id).remove();
			return true;
		},
		wholesale_add:function()
		{
			var html = '<tr><td><input type="text" name="_wholesale_qty[]" value="" class="layui-input" /></td><td><input type="text" name="_wholesale_price[]" value="" class="layui-input" /></td><td><input type="button" value="删除" onclick="$.admin_list_edit.wholesale_delete(this)" class="layui-btn layui-btn-xs layui-btn-danger" /></td></tr>';
			$("#tradeprice").append(html);
		},
		wholesale_delete:function(obj)
		{
			$(obj).parent().parent().remove();
			return true;
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
		$.admin_list_edit.attr_info_product();
	}
});