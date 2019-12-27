/**
 * 附件资料管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年12月18日
**/
;(function($){
	$.admin_res = {
		edit_local:function(){
			var url = get_url('res','setting_remote_to_local');
			$.dialog.open(url,{
				'title':p_lang('编辑器附件本地化设置'),
				'width':'600px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_res.edit_local_save();
					return false;
				}
			});
		},
		/**
		 * 配置保存
		**/
		edit_local_save:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('res','setting_remote_to_local_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.close();
						$.dialog.tips('配置操作成功');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		/**
		 * 批量更新指定的附件
		**/
		pl_update:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return true;
			}
			var url = get_url("res","update_pl") + "&id="+$.str.encode(id);
			top.$.win(p_lang('附件批量更新中，请不要关掉这个页面'),url,{'is_max':true,'win_max':false,'width':600,'height':400});
		},
		/**
		 * 批量删除指定的附件
		**/
		pl_delete:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return false;
			}
			$.dialog.confirm(p_lang('确定要删除选中的附件吗？删除后是不可恢复的'),function(){
				var url = get_url("res","delete_pl") + "&id="+$.str.encode(id);
				$.phpok.json(url,function(rs){
					if(rs.status == 'ok'){
						$.dialog.tips(p_lang('批量删除附件操作成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				})
			});
		},
		/**
		 * 文件删除
		**/
		file_delete:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除此附件吗？删除后不能恢复'),function(){
				url = get_url('upload','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status == 'ok'){
						$.dialog.tips(p_lang('附件删除成功'),function(){
							$("#thumb_"+id).remove();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				})
			});
		},
		/**
		 * 附件预览
		**/
		preview_attr:function(id)
		{
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':p_lang('预览附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':false,
				'button': [{
					'name': p_lang('下载原文件'),
					'callback': function () {
						$.phpok.open(get_url('res','download','id='+id));
						return false;
					},
				}],
				'okVal':p_lang('关闭'),
				'ok':true
			});
		},
		/**
		 * 更新全部附件信息
		**/
		update_pl_pictures:function()
		{
			$.dialog.prompt(p_lang('请输入要开始更新的图片数字ID<br/>默认表示更新全部图片（会占用比较多的时间）'),function(val){
				var url = get_url("res","update_pl","id=all");
				if(parseInt(val)>0){
					url +="&start_id="+val;
				}
				top.$.win(p_lang('附件批量更新中'),url,{'is_max':true,'win_max':false,'width':600,'height':400});
			},0).title(p_lang('批量更新图片规格'));
		},
		/**
		 * 添加附件
		**/
		add_file:function()
		{
			$.dialog.open(get_url('res','add'),{
				'title':p_lang('添加附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('执行附件上传'),
				'cancelVal':p_lang('取消上传并关闭'),
				'cancel':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.cancel();
					return true;
				}
			});
		},
		/**
		 * 编辑附件操作
		**/
		modify:function(id)
		{
			$.dialog.open(get_url('res','set','id='+id),{
				'title':p_lang('编辑附件信息'),
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':p_lang('提交'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe还没加载完毕呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'cancelVal':p_lang('取消修改'),
				'cancel':true
			});
		},
		/**
		 * 移动分类
		**/
		move_cate:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return false;
			}
			$.dialog({
				'title':p_lang('移动分类，请选择目标移动分类'),
				'content':document.getElementById('move_cate_html'),
				'lock':true,
				'width':'500px',
				'height':'100px',
				'cancel':function(){},
				'cancelVal':p_lang('取消移动'),
				'okVal':p_lang('执行'),
				'ok':function(){
					var newcate = $("input[name=newcate]:checked").val();
					var url = get_url('res','movecate')+"&tid="+$.str.encode(id)+"&newcate="+newcate;
					$.phpok.json(url,function(){
						$.input.checkbox_none('.checkbox input[type=checkbox]');
						$.dialog.tips(p_lang('分类移动成功'));
						return true;
					});
				}
			});
		},
		zipit:function(id,ext)
		{
			$.dialog.confirm(p_lang('确定初始化当前文件原图大小吗？'),function(){
				var width = $("#resize").val();
				var url = get_url('res','resize','id='+id+'&width='+width);
				if(ext == 'jpg' || ext == 'jpeg'){
					url += "&ptype="+$("#ptype").val();
				}
				var tip = $.dialog.tips(p_lang('正在初始化图片，请稍候…'));
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						$.dialog.tips('图片初始化成功');
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		clear_files:function()
		{
			$.win(p_lang('清理未使用文件'),get_url('res','clear'));
		}

	}
	$(document).ready(function(){
		layui.use('laydate',function(){
			var laydate = layui.laydate;
			if($("#start_date").length > 0){
				laydate.render({
                    elem: '#start_date',
                });
			}
			if($("#stop_date").length > 0){
                laydate.render({
                    elem: '#stop_date',
                });
			}
		});

	});
})(jQuery);
