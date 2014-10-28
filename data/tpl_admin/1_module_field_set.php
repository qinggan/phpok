<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $this->output("head","file"); ?>
<script type="text/javascript">
function check_save()
{
	var id = $("#id").val();
	if(!id || id == "undefined")
	{
		$.dialog.alert("未指定ID！");
		return false;
	}
	var title = $("#title").val();
	if(!title || title == "undefined")
	{
		$.dialog.alert("名称不能为空");
		return false;
	}
	return true;
}
</script>
<div class="tips clearfix">
	您当前的位置：
	<a href="<?php echo phpok_url(array('ctrl'=>'module'));?>">模块管理</a>
	&raquo; <a href="<?php echo phpok_url(array('ctrl'=>'module','func'=>'fields','id'=>$m_rs['id']));?>" title="<?php echo $m_rs['title'];?>"><?php echo $m_rs['title'];?></a>
	&raquo; 修改字段<span class="gray i">（<?php echo $rs['identifier'];?>）</span>
</div>

<form method="post" action="<?php echo phpok_url(array('ctrl'=>'module','func'=>'field_edit_save'));?>" onsubmit="return check_save();">
<input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
<div class="table">
	<div class="title">
		字段名称：
		<span class="note">设置一个名称，该名称在表单的头部信息显示</span></span>
	</div>
	<div class="content">
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td><input type="text" id="title" name="title" class="default" value="<?php echo $rs['title'];?>" /></td>
			<td><div id="title_note"></div></td>
		</tr>
		</table>
	</div>
</div>

<div class="table">
	<div class="title">
		字段备注：
		<span class="note">仅限后台管理使用，用于查看这个字段主要做什么</span></span>
	</div>
	<div class="content"><input type="text" id="note" name="note" class="long" value="<?php echo $rs['note'];?>" /></div>
</div>

<div class="table">
	<div class="title">
		表单类型：
		<span class="note">请选择字段要使用的表单 <span class="red n">添加完后，将不允许再修改</span></span>
	</div>
	<div class="content">
		<table>
		<tr>
			<td><select id="form_type" name="form_type" onchange="_phpok_form_opt(this.value,'form_type_ext','<?php echo $id;?>','module')">
					<option value="">请选择表单</option>
					<?php $form_list_id["num"] = 0;$form_list=is_array($form_list) ? $form_list : array();$form_list_id["total"] = count($form_list);$form_list_id["index"] = -1;foreach($form_list AS $key=>$value){ $form_list_id["num"]++;$form_list_id["index"]++; ?>
					<option value="<?php echo $key;?>"<?php if($key == $rs['form_type']){ ?> selected<?php } ?>><?php echo $value;?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		</table>
	</div>
</div>

<div id="form_type_ext" style="display:none;"></div>

<div class="table">
	<div class="title">
		<span class="edit">
			表单样式（CSS）：
			<span class="note">不能超过250个字符，不熟悉CSS，请查手册：<a href="http://www.phpok.com" target="_blank">phpok.com</a></span>
		</span>
	</div>
	<div class="content"><input type="text" id="form_style" name="form_style" class="long" value="<?php echo $rs['form_style'];?>" /></div>
</div>

<div class="table">
	<div class="title">
		表单默认值：
		<span class="note">设置表单默认值，如果表单中有多个值，请用英文逗号隔开</span>
	</div>
	<div class="content"><input type="text" id="content" name="content" class="long" value="<?php echo $rs['content'];?>" /></div>
</div>


<div class="table">
	<div class="title">
		接收数据格式化：
		<span class="note">设置文本常见格式，如HTML，整型，浮点型等</span>
	</div>
	<div class="content">
		<select name="format" id="format">
			<?php $format_list_id["num"] = 0;$format_list=is_array($format_list) ? $format_list : array();$format_list_id["total"] = count($format_list);$format_list_id["index"] = -1;foreach($format_list AS $key=>$value){ $format_list_id["num"]++;$format_list_id["index"]++; ?>
			<option value="<?php echo $key;?>"<?php if($rs['format'] == $key){ ?> selected<?php } ?>><?php echo $value;?></option>
			<?php } ?>
		</select>
	</div>
</div>

<div class="table">
	<div class="title">
		排序：
		<span class="note">值越小越往前靠，最小值为0，最大值为255</span>
	</div>
	<div class="content"><input type="text" id="taxis" name="taxis" class="short" value="<?php echo $rs['taxis'] ? $rs['taxis'] : 255;?>" /></div>
</div>

<div class="table">
	<div class="title">
		前端处理：
		<span class="note">设置前端是否可用，如果需要前端加载相应属性请在这里操作！</span>
	</div>
	<div class="content">
		<table>
		<tr>
			<td><label><input type="radio" name="is_front" value="0"<?php if(!$rs['is_front']){ ?> checked<?php } ?> />禁用</label></td>
			<td><label><input type="radio" name="is_front" value="1"<?php if($rs['is_front']){ ?> checked<?php } ?> />使用</label></td>
		</tr>
		</table>
	</div>
</div>

<div class="table">
	<div class="content">
		<br />
		<input type="submit" value="提 交" class="submit" />
		<br />
	</div>
</div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	_phpok_form_opt("<?php echo $rs['form_type'];?>","form_type_ext",'<?php echo $id;?>','module');
});
</script>
<?php $this->output("foot","file"); ?>