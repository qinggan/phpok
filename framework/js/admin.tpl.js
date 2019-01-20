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
			layer.prompt(notice,function(val){
				if(!val || val == undefined){
					val = title;
				}
				if(val == title){
					layer.alert("新旧名称一样");
					return false;
				}
				var url = get_url("tpl","rename","id="+id+"&folder="+$.str.encode(folder)+"&old="+$.str.encode(title)+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
					return false;
				})
			},title);
		},
		
		del:function(id,folder,title)
		{
			layer.confirm(p_lang('确定要删除文件（夹）{title}吗？<br>删除后是不能恢复的！','<span class="red">'+title+'</span> '),function(){
				if(!title){
					layer.alert("操作异常！");
					return false;
				}
				var url = get_url("tpl","delfile","id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
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
			layer.prompt(p_lang('请填写要创建的文件夹名称，<span class="red">仅支持数字，字母及下划线</span>：'),function(val){
				if(!val || val == "undefined"){
					layer.alert("文件夹名称不能为空");
					return false;
				}
				var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&type=folder&title="+$.str.encode(val);
				var url = get_url("tpl","create",url_ext);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
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
			layer.prompt(tip,function(val){
				if(!val || val == "undefined"){
					layer.alert("文件名称不能为空");
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
					layer.alert(rs.info);
					return false;
				});
			});
		},
		
		view:function(url)
		{
			var html = '<img src="'+url+'" border="0" />';
			layer.through({
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
			var url = get_url('tpl','edit','id='+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
			$.win(p_lang('编辑')+"_"+title,url);
			
			/*var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var title = p_lang('编辑文件：{title} 在线编辑请确保文件有写入权限','<span class="red">'+title+'</span>');
			$.dialog.open(get_url("tpl","edit",url_ext),{
				'width':'1000px',
				'height':'700px',
				'lock':true,
				'title':title,
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
				'cancelVal':p_lang('取消并关闭窗口')
			});*/
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
			layer.confirm(tip,function(){
				var url = get_url("tpl","delete","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('风格删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					layer.alert(rs.content);
					return false;
				});
			});
		},
		tpl_set:function(id)
		{
			$.win(p_lang('风格编辑 #'+id),get_url('tpl','set','id='+id));
		},
		
		tpl_filelist:function(id)
		{
			let url = get_url('tpl','list','id='+id);
			$.win(p_lang('文件管理 #'+id),get_url('tpl','list','id='+id));
		},
		
		set_folder:function(val)
		{
			var str = $("#folder_change").val();
			if(!str || str == "undefined"){
				$("#folder_change").val(val);
				return true;
			}
			if(str == val){
				$("#folder_change").val("");
				return true;
			}
			var list = str.split(",");
			if($.inArray(val,list) > 0){
				var nlist = new Array();
				var m = 0;
				for(var i in list){
					if(list[i] != val){
						nlist[m] = list[i];
						m++;
					}
				}
				str = nlist.join(",");
				$("#folder_change").val(str);
				return true;
			}
			str += ","+val;
			$("#folder_change").val(str);
			return true;
		},
		save:function()
		{
			var title = $("#title").val();
			if(!title){
				$.dialog.alert(p_lang('名称不能为空'));
				return false;
			}
			var folder = $("#folder").val();
			if(!folder){
				$.dialog.alert(p_lang('文件夹不能为空'));
				return false;
			}
			var ext = $("#ext").val();
			if(!ext){
				$.dialog.alert(p_lang('后缀不允许为空'));
				return false;
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('tpl','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('操作成功',function(){
							$.admin.reload(get_url('tpl'));
							$.admin.close(get_url('tpl'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);