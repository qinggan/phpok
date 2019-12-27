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

		add:function()
		{
			var url = get_url('tpl','set');
			$.dialog.open(url,{
				'title':p_lang('添加新风格'),
				'width':'800px',
				'height':'472px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_tpl.save();
					return false;
				},
				'okVal':p_lang('提交保存风格'),
				'cancel':true
			})
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
					if(rs.status){
						$.dialog.tips(p_lang('风格删除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				});
			});
		},
		tpl_set:function(id)
		{
			var url = get_url('tpl','set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('风格编辑')+"_#"+id,
				'width':'800px',
				'height':'472px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.$.admin_tpl.save();
					return false;
				},
				'okVal':p_lang('提交保存风格'),
				'cancel':true
			})
		},
		
		tpl_filelist:function(folder)
		{
			$.win(p_lang('文件管理'),get_url('filemanage','','folder='+$.str.encode(folder)));
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
			var opener = $.dialog.opener;
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
						var id = $("#id").val();
						var tip = (id && id>0) ? p_lang('模板风格编辑成功') : p_lang('风格添加成功');
						$.dialog.alert(tip,function(){
							opener.$.phpok.reload();
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