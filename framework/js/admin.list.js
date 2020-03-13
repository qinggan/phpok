/**
 * 内容管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年04月21日
**/
;(function($){
	$.phpok_list = {
		set:function(id)
		{
			var url = get_url('list','set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('编辑项目') +" #"+id,
				'lock':true,
				'width':'780px',
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
				'cancel':true
			});
		},
		tag:function()
		{
			var url = get_url('tag','open');
			$.dialog.open(url,{
				'title':p_lang('标签选择 '),
				'lock':true,
				'width':'600px',
				'height':'500px',
				'cancel':true,
				'cancel':p_lang('关闭')
			})
		},
		tag_append:function(val,cut_identifier)
		{
			var old = $("input[name=tag]").val();
			if(!old){
				$("input[name=tag]").val(val);
				return true;
			}
			if(!cut_identifier || cut_identifier == 'undefined'){
				cut_identifier = ',';
			}
			var lst = old.split(cut_identifier);
			var total = lst.length;
			if(total>=10){
				$.dialog.tips(p_lang('超出系统限制，请删除一些不常用的标签'));
				return false;
			}
			var status = true;
			for(var i in lst){
				if(lst[i] && $.trim(lst[i]) == val){
					status = false;
				}
			}
			if(!status){
				$.dialog.tips(p_lang('标签已经存在，不支持重复添加'));
				return false;
			}
			$("input[name=tag]").val(old+""+cut_identifier+""+val);
			return true;
		},
		sort:function(obj,id)
		{
			var val = $(obj).val();
			var url = get_url('list','content_sort','sort['+id+']='+val.toString());
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(p_lang('排序更新成功')).follow($(obj)[0]);
					return true;
				}
				$.dialog.alert(data.content);
				return false;
			})
		}
	};

	$.admin_list = {
		single_save:function()
		{
			var loading_action;
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','single_save'),
				'type':'post',
				'dataType':'json',
				'beforeSubmit':function(){
					loading_action = $.dialog.tips(p_lang('正在保存数据，请稍候…')).time(30).lock();
				},
				'success':function(rs){
					if(loading_action){
						loading_action.close();
					}
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					var pid = $("#project_id").val();
					var url = get_url('list','action','id='+pid);
					var id = $("#id").val();
					if(id){
						$.dialog.alert(p_lang('内容信息修改成功'),function(){
							$.admin.reload(url);
							$.admin.close(url);
						},'succeed');
						return false;
					}
					$.dialog.through({
						'icon':'succeed',
						'content':p_lang('内容添加操作成功，请选择继续添加或返回列表'),
						'ok':function(){
							$.admin.reload(url);
							$.phpok.reload();
						},
						'okVal':p_lang('继续添加'),
						'cancel':function(){
							$.admin.reload(url);
							$.admin.close(url);
						},
						'cancelVal':p_lang('返回列表'),
						'lock':true
					});
				}
			});
			return false;
		},

		/**
		 * 删除主题
		 * @参数 pid 项目ID
		 * @参数 tid 主题ID
		**/
		single_delete:function(pid,tid)
		{
			$.dialog.confirm(p_lang('确定要删除ID #{tid} 的信息吗？<br/>删除后数据是不能恢复的',tid),function(){
				var url = get_url('list','single_delete','pid='+pid+"&id="+tid);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
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
		 * 评论维护
		 * @参数 id 主题ID
		**/
		reply_it:function(id)
		{
			$.dialog.open(get_url('list','comment','id='+id),{
				'title':p_lang('评论#{id}',id),
				'lock':true,
				'width':'80%',
				'height':'80%',
				'cancel':true
			});
		},

		/**
		 * 生成随机码
		**/
		rand_identifier:function()
		{
			var info = $.phpok.rand(3,'letter')+''+$.phpok.rand(7,'fixed');
			$("#identifier").val(info);
			return true;
		},

		/**
		 * 快速添加扩展字段
		**/
		update_select_add:function(module)
		{
			var val = $("#_tmp_select_add").val();
			if(!val){
				$.dialog.alert(p_lang('请选择要添加的扩展'));
				return false;
			}
			ext_add2(val,module);
		},

		extitle_view:function(id,pid)
		{
			var url = get_url('form','preview','id='+id+"&pid="+pid);
			$.dialog.open(url,{
				'title':p_lang('预览'),
				'lock':true,
				'width':'750px',
				'height':'650px',
				'ok':true
			});
		},
		//取用或禁用状态
		status:function(id)
		{
			var url = get_url("list","content_status","id="+id);
			$.phpok.json(url,function(data){
				if(!data.status){
					$.dialog.alert(data.info);
					return false;
				}
				var old_value = $("#status_"+id).attr("value");
				var new_value = old_value == '1' ? '0' : '1';
				$("#status_"+id).removeClass('status'+old_value).addClass("status"+new_value).attr('value',new_value);
				//更新Message
				$.phpok.message('pendding');
			})
		},
		cate:function(id)
		{
			$.dialog.prompt(p_lang('请填写分类名称'),function(val){
				var url = get_url('cate','qsave','root_id='+id+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						var html = '<option value="'+rs.info+'">'+val+'</option>';
						$("#cate_id,#ext_cate_id").append(html);
						layui.form.render();
						$.dialog.tips(p_lang('分类添加成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		subcate:function()
		{
			var id = $("#cate_id").val();
			if(!id){
				$.dialog.alert(p_lang('请选择分类'));
				return false;
			}
			var layer = $("select[name=cate_id]").find("option[value="+id+"]").attr("data-layer");
			layer = parseInt(layer);
			var is_end = $("select[name=cate_id]").find("option[value="+id+"]").attr("data-isend");
			var space = '';
			if(layer>0){
				for(var i=0;i<layer;i++){
					space += '&nbsp; &nbsp;│';
				}
			}
			if(layer>0 && is_end){
				space += '&nbsp; &nbsp;│';
			}
			space += '&nbsp; &nbsp;├';
			$.dialog.prompt(p_lang('请填写分类名称'),function(val){
				var url = get_url('cate','qsave','root_id='+id+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						var html = '<option value="'+rs.info+'" data-layer="'+(layer+1).toString()+'" data-isend="0">'+space+''+val+'</option>';
						$("select[name=cate_id]").find("option[value="+id+"]").after(html);
						$("#ext_cate_id").find("option[value="+id+"]").after(html);
						layui.form.render();
						$.dialog.tips(p_lang('分类添加成功'));
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	};

	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use(['form','laydate'],function(){
				var laydate = layui.laydate;
				if($("#dateline_start").length > 0){
					laydate.render({
	                    elem: '#dateline_start',
	                });
				}
				if($("#dateline_stop").length > 0){
	                laydate.render({
	                    elem: '#dateline_stop',
	                });
				}
				layui.form.render();
			});
		}
		$("input[name=taxis]").on('keyup',function(){
			var val = $(this).val();
			val = val.replace(/[^0-9-]+/,'');
			$(this).val(val);
			//this.value= ($(this).val()).replace();
		}).on('keydown',function(){
			var val = $(this).val();
			val = val.replace(/[^0-9-]+/,'');
			$(this).val(val);
		}).on('focus',function(){
			$(this).select();
		});
	});

})(jQuery);

function preview_attr(id)
{
	var url = get_url("res_action","preview") + "&id="+id;
	$.dialog.open(url,{
		title: p_lang('预览'),
		lock : true,
		width: "700px",
		height: "70%",
		resize: true
	});
}