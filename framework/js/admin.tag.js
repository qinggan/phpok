/**
 * Tag标签的增删查改操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @日期 2017年04月20日
**/
;(function($){
	$.admin_tag = {
		add:function()
		{
			var url = get_url('tag','set');
			$.dialog.open(url,{
				'title':p_lang('添加标签'),
				'width':'560px',
				'height':'620px',
				'lock':true,
				'okVal':p_lang('提交保存'),
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
		edit:function(id)
		{
			var url = get_url('tag','set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('修改标签'),
				'width':'560px',
				'height':'620px',
				'lock':true,
				'okVal':p_lang('提交保存'),
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
		del:function(id,title)
		{
			$.dialog.confirm(p_lang('确定要删除标签 {title} 吗？删除后相关标签数据也会删除','<span class="red">'+title+'</span>'),function(){
				var url = get_url('tag','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		config:function()
		{
			var url = get_url('tag','config');
			$.dialog.open(url,{
				'title':p_lang('配置标签参数'),
				'width':'500px',
				'height':'300px',
				'lock':true,
				'okVal':p_lang('提交保存'),
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
		selected:function(val,cut_identifier)
		{
			var opener = $.dialog.opener;
			var old = opener.$("input[name=tag]").val();
			if(!old){
				opener.$("input[name=tag]").val(val);
				$.dialog.tips(p_lang('添加成功')).position('50%','1%');
				return true;
			}
			if(!cut_identifier || cut_identifier == 'undefined'){
				cut_identifier = ',';
			}
			var lst = old.split(cut_identifier);
			var total = lst.length;
			if(total>=10){
				$.dialog.alert(p_lang('超出系统限制，请删除一些不常用的标签'));
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
			opener.$("input[name=tag]").val(old+""+cut_identifier+""+val);
			$.dialog.tips(p_lang('添加成功')).position('50%','1%');
			return true;
		},
		titles:function(id,title)
		{
			$.win(p_lang('标签')+"_"+title,get_url('tag','list','id='+id));
		},
		delete_title:function(tag_id,title_id)
		{
			$.dialog.confirm(p_lang('确定要删除')+"_#"+title_id,function(){
				var url = get_url('tag','del_stat','tag_id='+tag_id+"&title_id="+title_id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$("#tag_"+title_id).remove();
						$.dialog.tips('删除成功');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		delete_title2:function(tag_id){
			var ids = $.input.checkbox_join();
			if(!ids){
				$.dialog.alert('未指定要操作的主题');
				return false;
			}
			$.dialog.confirm('确定要删除选中的主题吗？',function(){
				var url = get_url('tag','del_stat','tag_id='+tag_id+"&title_id="+$.str.encode(ids));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips('删除成功');
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		add_title:function(id,title)
		{
			var url = get_url('tag','titles','tag_id='+id);
			$.dialog.open(url,{
				'title':p_lang('添加主题')+"_#"+title,
				'lock':true,
				'width':'700px',
				'height':'500px'
			});
		},
		add_cate:function(id,title)
		{
			var url = get_url('tag','cates','tag_id='+id);
			$.dialog.open(url,{
				'title':p_lang('添加分类')+"_#"+title,
				'lock':true,
				'width':'700px',
				'height':'500px'
			});
		},
		add_project:function(id,title)
		{
			var url = get_url('tag','projects','tag_id='+id);
			$.dialog.open(url,{
				'title':p_lang('添加项目')+"_#"+title,
				'lock':true,
				'width':'700px',
				'height':'500px'
			});
		},
		add_this:function(tag_id,title_id)
		{
			var url = get_url('tag','add_it','tag_id='+tag_id+"&title_id="+title_id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('添加成功')).position('49%','5%');
				$("#t_"+title_id).remove();
			});
		},
		nodelist:function(tag_id,title)
		{
			var url = get_url('tag','nodelist','tag_id='+tag_id);
			$.win(p_lang('节点管理_#{title}',title),url);
		},
		node_delete:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除节点 [id] 信息吗？删除后数据不能恢复',id),function(){
				var url = get_url('tag','node_delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					$.dialog.tips(p_lang('节点删除成功'));
					$.phpok.reload();
				})
			});
		},
		node_title:function(id)
		{
			var url = get_url('tag','node_title','id='+id);
			$.dialog.open(url,{
				'title':p_lang('绑定')+"_#"+id,
				'lock':true,
				'width':'700px',
				'height':'500px'
			});
		},
		add_node_title:function(node_id,title_id)
		{
			var url = get_url('tag','add_it_node','node_id='+node_id+"&title_id="+title_id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$.dialog.tips(p_lang('添加成功')).position('49%','5%');
				$("#t_"+title_id).remove();
			});
		},
		node_delete_ids:function(id,node_id)
		{
			var url = get_url('tag','node_delete_ids','node_id='+node_id+"&id="+id);
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				$("#t"+node_id+"_"+id).remove();
				return true;
			})
		},
		node_set:function(id,type)
		{
			if(!type || type == 'undefined'){
				type = 'edit';
			}
			if(type == 'edit'){
				var url = get_url('tag','node_set','id='+id);
				var title = p_lang('编辑节点')+"_#"+id;
			}else{
				var url = get_url('tag','node_set','tag_id='+id);
				var title = p_lang('添加节点');
			}
			$.dialog.open(url,{
				'title':title,
				'width':'700px',
				'height':'480px',
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
				'okVal':p_lang('提交保存'),
				'cancel':true
			})
		}
	}
})(jQuery);