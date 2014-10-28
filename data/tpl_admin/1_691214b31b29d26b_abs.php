<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><input type="hidden" name="<?php echo $_rs['identifier'];?>" id="<?php echo $_rs['identifier'];?>" value="<?php echo $_rs['content'];?>" />
<div class="_e_upload">
	<div class="_select">
		<table>
		<tr>
			<td><div id="<?php echo $_rs['identifier'];?>_picker"></div></td>
			<td><button id="<?php echo $_rs['identifier'];?>_submit" type="button" class="button">开始上传</button></td>
			<td><button id="select_res_<?php echo $_rs['identifier'];?>" class="button" type="button">选择<?php echo $_rs['upload_type']['name'];?></button></td>
			<td id="<?php echo $_rs['identifier'];?>_sort" style="display:none"><button id="select_res_<?php echo $_rs['identifier'];?>" onclick="obj_<?php echo $_rs['identifier'];?>.sort()" class="button" type="button">排序</button></td>
		</tr>
		</table>
	</div>
	<div class="_progress" id="<?php echo $_rs['identifier'];?>_progress"></div>
	<div class="_list" id="<?php echo $_rs['identifier'];?>_list"></div>
</div>
<script type="text/javascript">
var obj_<?php echo $_rs['identifier'];?>;
$(document).ready(function(){
	obj_<?php echo $_rs['identifier'];?> = new $.phpok_upload({
		"multi"							: <?php echo $_rs['is_multiple'] ? 'true' : 'false';?>,
		"id"							: "<?php echo $_rs['identifier'];?>",
		"pick"							: "#<?php echo $_rs['identifier'];?>_picker",
		"swf"							: "js/webupload/upload.swf",
		"server"						: "<?php echo phpok_url(array('ctrl'=>'upload','func'=>'save'));?>",
		"filetypes"						: "<?php echo $_rs['upload_type']['ext'];?>",
		"cateid"						: "<?php echo $_rs['cate_id'];?>",
		"formData"						:{'<?php echo session_name();?>':'<?php echo session_id();?>'}
	});
	obj_<?php echo $_rs['identifier'];?>.preview_res();

	$("#select_res_<?php echo $_rs['identifier'];?>").click(function(){
		var url = "<?php echo phpok_url(array('ctrl'=>'open','func'=>'input','type'=>$_rs['upload_type']['id'],'id'=>$_rs['identifier'],'tpl'=>'open_image2'));?>";
		url += "&is_multiple=<?php echo $_rs['is_multiple'] ? 1 : 0;?>";
		var c = $("#<?php echo $_rs['identifier'];?>").val();
		$.dialog.data('<?php echo $_rs['identifier'];?>',c);
		$.dialog.open(url,{
			title: "<?php echo $_rs['upload_type']['name'];?>管理器",
			lock : true,
			width: "700px",
			height: "70%",
			ok: function(){
				obj_<?php echo $_rs['identifier'];?>.preview_res();
			}
		});
	});
});
</script>

