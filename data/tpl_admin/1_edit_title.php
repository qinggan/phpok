<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $this->output("head_open","file"); ?>
<style type="text/css">body{overflow:hidden;margin:2px 0 0 2px;}</style>
<script type="text/javascript" src="<?php echo add_js('ueditor/dialogs/internal.js');?>"></script>
<script type="text/javascript">
var project_id = "<?php echo $project_id;?>";
function update_cate2(val)
{
	$("#cate_id").val(val);
}
function update_cate()
{
	var project_id = $("#project_id").val();
	var url = get_url('uedit','info_cate');
	if(project_id && project_id != '0')
	{
		url += "&project_id="+project_id;
	}
	var rs = json_ajax(url);
	if(rs.status == 'ok')
	{
		var cate_id = $("#cate_id").val();
		var lst = rs.content;
		var html = '<select id="cate_id2" onchange="update_cate2(this.value)">';
		html += '<option value="">不限</option>';
		for(var i in lst)
		{
			html+='<option value="'+lst[i]['id']+'"';
			if(lst[i]['id'] == cate_id)
			{
				html += " selected";
			}
			html+='>'+lst[i]['title']+'</option>';
		}
		html += '</select>';
		$("#show_cate").html(html).show().css("padding-left","5px");
	}
	else
	{
		$("#show_cate").html('').hide();
	}
}
dialog.onok = function(){
	var vals = $.input.checkbox_join("filelist");
	if(!vals)
	{
		alert("请选择要插入的主题");
		return false;
	}
	var list = vals.split(",");
	if(list.length < 2)
	{
		var title = $("#t_"+list[0]).attr('title');
		if(!title)
		{
			alert('主题选择异常，请检查');
			return false;
		}
		var htm = '[title:'+list[0]+']'+title+'[/title]';
	}
	else
	{
		var htm = "";
		for(var i in list)
		{
			var title = $("#t_"+list[i]).attr("title");
			if(!title)
			{
				continue;
			}
			htm += '<p>[title:'+list[i]+']'+title+'[/title]</p>'+"\n";
		}
	}
	editor.execCommand('inserthtml',htm);
	dialog.close();
};
</script>
<div class="tips">
<table cellpadding="0" cellspacing="0">
<tr>
	<form method="post" action="<?php echo admin_url('uedit','info');?>">
	<input type="hidden" name="cate_id" id="cate_id" value="<?php echo $cate_id;?>" />
	<td style="padding-top:3px;"><input type="text" id="keywords" name="keywords" value="<?php echo $keywords;?>" placeholder="输入要搜索的关键字" /></td>
	<td style="padding-left:5px;"><select id="project_id" name="project_id" onchange="update_cate()">
			<option value="0">不限</option>
			<?php $projectlist_id["num"] = 0;$projectlist=is_array($projectlist) ? $projectlist : array();$projectlist_id["total"] = count($projectlist);$projectlist_id["index"] = -1;foreach($projectlist AS $key=>$value){ $projectlist_id["num"]++;$projectlist_id["index"]++; ?>
			<option value="<?php echo $value['id'];?>"<?php if($value['id'] == $project_id){ ?> selected="selected"<?php } ?>><?php echo $value['space'];?><?php echo $value['title'];?></option>
			<?php } ?>
		</select>
	</td>
	<td id="show_cate" style="display:none;"></td>
	<td><input type="submit" value="搜索" class="button" /></td>
	</form>
</tr>
</table>
</div>
<div class="list" id="filelist">
<table width="100%" cellpadding="0" cellspacing="0" class="list">
<tr>
	<th width="30px" height="30px">&nbsp;</th>
	<th class="lft">主题</th>
	<th width="30px" height="30px">&nbsp;</th>
	<th class="lft">主题</th>
</tr>
<tr>
	<?php $rslist_id["num"] = 0;$rslist=is_array($rslist) ? $rslist : array();$rslist_id["total"] = count($rslist);$rslist_id["index"] = -1;foreach($rslist AS $key=>$value){ $rslist_id["num"]++;$rslist_id["index"]++; ?>
	<td class="center"><input type="checkbox" value="<?php echo $value['id'];?>" id="t_<?php echo $value['id'];?>" title="<?php echo $value['title'];?>" /></td>
	<td class="lft" title="添加时间：<?php echo date('Y-m-d H:i',$value['dateline']);?>&#10;查看次数：<?php echo $value['hits'];?>"><label for="t_<?php echo $value['id'];?>"><?php echo phpok_cut($value['title'],'30','…');?></label>
	</td>
	<?php if($rslist_id['num'] % 2 == '' && $rslist_id['num'] != $rslist_id['total']){ ?></tr><tr><?php } ?>
	<?php } ?>
</tr>
</table>
</div>
<div class="table"><?php $this->output("pagelist","file"); ?></div>
<script type="text/javascript">
$(document).ready(function(){
	if(project_id)
	{
		update_cate();
	}
});
</script>
<?php $this->output("foot_open","file"); ?>