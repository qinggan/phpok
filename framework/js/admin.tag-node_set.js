/**
 * 标签节点编辑涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月9日
**/
function save()
{
	var opener = $.dialog.opener;
	$("#post_save").ajaxSubmit({
		'url':get_url('tag','node_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				var tipinfo = $("#id").length > 0 ? p_lang('节点编辑成功') : p_lang('节点添加成功');
				$.dialog.tips(tipinfo,function(){
					opener.$.phpok.reload();
				});
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
}
function update_catelist(obj)
{
	var value = $(obj).val();
	var url = get_url('tag','catelist','pid='+value);
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		var list = rs.info;
		var html = '<select id="cid" name="cid" lay-ignore style="border:1px solid #D2D2D2;line-height:32px;width:100%;">';
		if(!list){
			html += '<option value="">'+p_lang('无分类')+'</option>';
		}else{
			html += '<option value="">'+p_lang('不关联分类')+'</option>';
			for(var i in list){
				html += '<option value="'+list[i].id+'">'+list[i]._space+''+list[i].title+'</option>';
			}
		}
		html += '</select>';
		$("#cid_html").html(html);
	});
}
