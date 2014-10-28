<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php if($edit_rs['is_multiple']){ ?>
<script type="text/javascript">
function action_<?php echo $edit_rs['identifier'];?>_show()
{
	var tmp_id = $("#<?php echo $edit_rs['identifier'];?>").val();
	if(tmp_id)
	{
		var url = get_url("inp")+"&type=user&content="+$.str.encode(tmp_id);
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			var lst = rs.content;
			var c = '';
			var m = 1;
			for(var i in lst)
			{
				var class_cate_id = "cate_"+(m%9).toString();
				c += '<li id="<?php echo $edit_rs['identifier'];?>_div_'+lst[i]['id']+'">';
				c += '<div class="cate '+class_cate_id+'"><a href="javascript:phpok_user_preview(\''+lst[i]['id']+'\');void(0);">'+lst[i]['user']+'</a></div>';
				c += '<div class="cate_add"><a href="javascript:phpok_user_delete(\'<?php echo $edit_rs['identifier'];?>\',\''+lst[i]['id']+'\');void(0);" title="删除"><img src="images/page_delete.png" border="0" alt="" /></a></div>';
				c += '</li>';
				m++;
			}
			$("#<?php echo $edit_rs['identifier'];?>_div").html(c);
		}
	}
}
$(document).ready(function(){
	$("#_phpok_button_user_<?php echo $edit_rs['identifier'];?>").click(function(){
		var url = get_url("open","user") + "&id=<?php echo $edit_rs['identifier'];?>&multi=1";
		$.dialog.data("phpok_user_<?php echo $edit_rs['identifier'];?>",$("#<?php echo $edit_rs['identifier'];?>").val());
		$.dialog.open(url,{
			title: "会员管理器",
			lock : true,
			width: "700px",
			height: "70%",
			resize: false
			,"ok":function(){
				var data = $.dialog.data("phpok_user_<?php echo $edit_rs['identifier'];?>");
				$("#<?php echo $edit_rs['identifier'];?>").val(data);
				action_<?php echo $edit_rs['identifier'];?>_show();
				$.dialog.data("phpok_user_<?php echo $edit_rs['identifier'];?>","");
			}
			,"okVal":"确定"
		});
	});
	action_<?php echo $edit_rs['identifier'];?>_show();
});
</script>
<input type="hidden" name="<?php echo $edit_rs['identifier'];?>" id="<?php echo $edit_rs['identifier'];?>" value="<?php echo $edit_rs_content;?>" />
<ul class="layout_user">
	<li style="margin-top:5px;"><input id="_phpok_button_user_<?php echo $edit_rs['identifier'];?>" type="button" value="请选择" class="button" /></li>
	<div id="<?php echo $edit_rs['identifier'];?>_div" class="user_selected_div"></div>
	<div class="clear"></div>
</ul>
<?php } else { ?>
<script type="text/javascript">
function action_<?php echo $edit_rs['identifier'];?>_show()
{
	var tmp_id = $("#<?php echo $edit_rs['identifier'];?>").val();
	if(tmp_id)
	{
		var url = get_url("inp")+"&type=user&content="+$.str.encode(tmp_id);
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			var lst = rs.content;
			for(var i in lst)
			{
				$("#title_<?php echo $edit_rs['identifier'];?>").val(lst[i]['user']);
			}
		}
	}
}
$(document).ready(function(){
	$("#_phpok_button_user_<?php echo $edit_rs['identifier'];?>").click(function(){
		var url = get_url("open","user") + "&id=<?php echo $edit_rs['identifier'];?>";
		$.dialog.open(url,{
			title: "会员管理器",
			lock : true,
			width: "700px",
			height: "70%",
			resize: false
			,"ok":function(){
				var data = $.dialog.data("phpok_user_<?php echo $edit_rs['identifier'];?>");
				$("#<?php echo $edit_rs['identifier'];?>").val(data);
				action_<?php echo $edit_rs['identifier'];?>_show();
				$.dialog.data("phpok_user_<?php echo $edit_rs['identifier'];?>","");
			}
			,"okVal":"确定"
		});
	});
	action_<?php echo $edit_rs['identifier'];?>_show();
});
</script>
<input type="hidden" name="<?php echo $edit_rs['identifier'];?>" id="<?php echo $edit_rs['identifier'];?>" value="<?php echo $edit_rs_content;?>" />
<table cellpadding="0" cellspacing="0" border="0">
<tr>
	<td><input type="text" id="title_<?php echo $edit_rs['identifier'];?>" name="title_<?php echo $edit_rs['identifier'];?>" style="width:200px;" disabled /></td>
	<td>&nbsp;</td>
	<td><input type="button" value="会员" class="btn" id="_phpok_button_user_<?php echo $edit_rs['identifier'];?>" /></td>
	<td>&nbsp;</td>
	<td><input type="button" value="删除" class="btn" onclick="$('#<?php echo $edit_rs['identifier'];?>').val('');$('#title_<?php echo $edit_rs['identifier'];?>').val('')" /></td>
</tr>
</table>
<?php } ?>