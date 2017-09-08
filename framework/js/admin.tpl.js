/**
 * 风格管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年06月29日
**/
;(function($){
	$.admin_tpl = {
		rename:function(id,folder,title,notice)
		{
			$.dialog.prompt(notice,function(val){
				if(!val || val == undefined){
					val = title;
				}
				if(val == title){
					$.dialog.alert("新旧名称一样");
					return false;
				}
				var url = get_url("tpl","rename","id="+id+"&folder="+$.str.encode(folder)+"&old="+$.str.encode(title)+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			},title);
		},
		del:function(id,folder,title)
		{
			$.dialog.confirm(p_lang('确定要删除文件（夹）{title}吗？<br>删除后是不能恢复的！','<span class="red">'+title+'</span> '),function(){
				if(!title){
					$.dialog.alert("操作异常！");
					return false;
				}
				var url = get_url("tpl","delfile","id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
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
		download:function(id,folder,title)
		{
			var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var url = get_url("tpl","download",url_ext);
			$.phpok.go(url);
		},
		folder_rename:function(id,folder,title)
		{
			var notice = p_lang('将文件夹{title}改名为：（仅支持字母、数字、下划线）',' <span class="red">'+title+'</span> ');
			this.rename(id,folder,title,notice);
		},
		file_rename:function(id,folder,title)
		{
			var notice = p_lang('将文件{title}改名为：<br><span class="red">仅支持字母、数字、下划线和点，注意扩展名必须填写</span>',' <span class="red">'+title+'</span> ');
			this.rename(id,folder,title,notice);
		},
		add_folder:function(id,folder)
		{
			$.dialog.prompt(p_lang('请填写要创建的文件夹名称，<span class="red">仅支持数字，字母及下划线</span>：'),function(val){
				if(!val || val == "undefined"){
					$.dialog.alert("文件夹名称不能为空");
					return false;
				}
				var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&type=folder&title="+$.str.encode(val);
				var url = get_url("tpl","create",url_ext);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		add_file:function(id,folder,ext)
		{
			if(!ext || ext == 'undefined'){
				ext = 'html';
			}
			var tip = p_lang('填写要创建的文件名，<span class="red">仅持数字，字母，下划线及点</span>：');
			$.dialog.prompt(tip,function(val){
				if(!val || val == "undefined"){
					$.dialog.alert("文件名称不能为空");
					return false;
				}
				var extlen = -(ext.length + 1);
				var val_t = val.substr(extlen);
				if(val_t != '.'+ext){
					val += '.'+ext;
				}
				var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&type=file&title="+$.str.encode(val);
				var url = get_url("tpl","create",url_ext);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		view:function(url)
		{
			var html = '<img src="'+url+'" border="0" />';
			$.dialog.through({
				title: p_lang('预览图片'),
				lock: true,
				content:html,
				width: '400px',
				height: '300px',
				resize: true
			});
		},
		edit:function(id,folder,title)
		{
			var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var url = get_url("tpl","edit",url_ext);
			$.dialog.open(url,{
				"title":p_lang('编辑文件：{title}','<span class="red">'+title+'</span>')+' '+p_lang('【在线编辑请确保文件有写入权限】'),
				"resize": true,
				"lock": true,
				"width":"80%",
				"height":"70%",
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('保存代码'),
				'cancel':true,
				'cancelVal':'关闭窗口'
			});
		},
		open_select:function(id,val)
		{
			var url = get_url('tpl','open','tpl_id='+val+"&id="+id);
			$.phpok.go(url);
		},
		phpok_input:function(val,id)
		{
			var obj = $.dialog.opener;
			obj.$("#"+id).val(val);
			$.dialog.close();
		},
		tpl_delete:function(id,title)
		{
			var tip = p_lang('确定要删除{title}吗？<br>删除后请手动删除相应文件目录',' <span class="red b">'+title+'</span> ');
			$.dialog.confirm(tip,function(){
				var url = get_url("tpl","delete","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status == 'ok'){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				});
			});
		},
		tpl_set:function(id)
		{
			$.phpok.go(get_url('tpl','set','id='+id));
		},
		tpl_filelist:function(id)
		{
			$.phpok.go(get_url('tpl','list','id='+id));
		}
	}
})(jQuery);