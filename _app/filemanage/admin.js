/**
 * 后面页面脚本_管理整个平台的文件，包括修改自身，仅限系统管理员
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年04月09日 15时31分
**/
;(function($){
	$.admin_filemanage = {
		add_file:function(folder)
		{
			var tip = p_lang('填写要创建的文件名（包括后缀）<br/><span class="red">仅持数字，字母，下划线及点</span>：');
			$.dialog.prompt(tip,function(val){
				if(!val || val == "undefined"){
					$.dialog.alert("文件名称不能为空");
					return false;
				}
				var url_ext = "folder="+$.str.encode(folder)+"&type=file&title="+$.str.encode(val);
				$.phpok.json(api_url("filemanage","create",url_ext),function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		add_folder:function(folder)
		{
			$.dialog.prompt(p_lang('请填写要创建的文件夹名称，<br/><span class="red">仅支持数字，字母及下划线</span>：'),function(val){
				if(!val || val == "undefined"){
					$.dialog.alert("文件夹名称不能为空");
					return false;
				}
				var url_ext = "folder="+$.str.encode(folder)+"&type=folder&title="+$.str.encode(val);
				$.phpok.json(api_url("filemanage","create",url_ext),function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		del:function(id,folder,title)
		{
			$.dialog.confirm(p_lang('确定要删除文件（夹）{title}吗？<br>删除后是不能恢复的！','<span class="red">'+title+'</span> '),function(){
				if(!title){
					$.dialog.alert(p_lang('操作异常，未指定文件'));
					return false;
				}
				var url = api_url("filemanage","delfile","folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('删除成功')).position('48%','5px');
						$("#"+id).remove();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		download:function(folder,title)
		{
			var url_ext = "folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var url = api_url("filemanage","download",url_ext);
			$.phpok.go(url);
		},
		edit:function(folder,title)
		{
			var url = get_url('filemanage','edit',"folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
			$.win(p_lang('编辑')+"_"+title,url);
		},
		edit_btn:function(code,editor)
		{
			if(code == 'left' || code == 'right' || code == 'center'){
				code = '<div style="text-align:'+code+'">$1</div>';
			}
			if(code == 'list'){
				code = "<ul>\n\t<li></li>\n\t<li></li>\n</ul>";
			}
			if(code == 'numberlist'){
				code = "<ol>\n\t<li></li>\n\t<li></li>\n</ol>";
			}
			var content = editor.getSelection();
			if(content){
				code = code.replace('$1',content);
			}else{
				code = code.replace('$1','');
			}
			editor.replaceSelection(code);
			editor.refresh();
			return true;
		},
		edit_config_info:function(obj,editor)
		{
			var val = $(obj).val();
			if(!val){
				return true;
			}
			var code = '{'+val+'}';
			editor.replaceSelection(code);
			editor.refresh();
			$(obj).val('');
			return true;
		},
		edit_datalist:function(obj,editor)
		{
			var val = $(obj).val();
			if(!val){
				return true;
			}
			var info = val.split(":");
			var html = '<!-- php:$info = phpok("'+info[0]+'") -->'+"\n";
			if(info[1] == 'arclist'){
				html += "<!-- loop from=$info.rslist key=$key value=$value id=$tmpid -->\n\n";
				html += "<!-- /loop -->";
			}else if(info[1] == 'catelist'){
				html += "<!-- loop from=$info.sublist key=$key value=$value id=$tmpid -->\n\n";
				html += "<!-- /loop -->";
			}else{
				html += "{$info.title}\n";
				html += "{debug $info}";
			}
			editor.replaceSelection(html);
			editor.refresh();
			$(obj).val('');
			return true;
		},
		
		folder_rename:function(folder,title)
		{
			var notice = p_lang('将文件夹{title}改名为：（仅支持字母、数字、下划线）',' <span class="red">'+title+'</span> ');
			this.rename(folder,title,notice);
		},
		
		file_rename:function(folder,title)
		{
			var notice = p_lang('将文件{title}改名为：<br><span class="red">仅支持字母、数字、下划线和点，注意扩展名必须填写</span>',' <span class="red">'+title+'</span> ');
			this.rename(folder,title,notice);
		},
		
		rename:function(folder,title,notice)
		{
			$.dialog.prompt(notice,function(val){
				if(!val || val == undefined){
					val = title;
				}
				if(val == title){
					layer.alert("新旧名称一样");
					return false;
				}
				var url = api_url("filemanage","rename","folder="+$.str.encode(folder)+"&old="+$.str.encode(title)+"&title="+$.str.encode(val));
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
		upfile:function(folder)
		{
			var url = get_url('filemanage','import','folder='+$.str.encode(folder));
			$.dialog.open(url,{
				'title':p_lang('上传文件'),
				'width':'500px',
				'height':'150px',
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
				'okVal':p_lang('开始上传'),
				'cancel':true
			})
		},
		vcode_act:function()
		{
			var url = api_url('filemanage','check');
			var vcode = $("#vcode").val();
			if(!vcode){
				$.dialog.alert(p_lang('请输入管理员二次密码认证'));
				return false;
			}
			url += "&code="+$.str.encode(vcode);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('验证通过，请稍候…'),function(){
						$.phpok.reload();
					}).lock();
					return false;
				}
				$.dialog.alert(rs.info);
				return false;
			});
			return false;
		},
		view:function(url)
		{
			var html = '<img src="'+url+'" border="0" style="max-width:100%" />';
			$.dialog.through({
				title: p_lang('预览图片'),
				lock: true,
				content:html,
				width: '60%',
				height: '60%',
				resize: true
			});
		}
	}
})(jQuery);
