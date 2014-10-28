<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><input type="hidden" name="ext_form_id" id="ext_form_id" value="width,height,is_code:checkbox,btn_image:checkbox,btn_video:checkbox,btn_file:checkbox,btn_page:checkbox,btn_info:checkbox,is_read:checkbox,etype,btn_page:checkbox,btn_tpl:checkbox,btn_map:checkbox,btn_info:checkbox" />
<div class="table">
	<div class="title">
		编辑器宽高设置：
		<span class="note">只需要填写数字，后台宽度强制970px</span>
	</div>
	<div class="content">
		<input type="text" name="width" id="width" value="<?php echo $rs['width'];?>" class="short" />
		&#215;
		<input type="text" name="height" id="height" value="<?php echo $rs['height'];?>" class="short" />
	</div>
</div>
<div class="table">
	<div class="title">
		编辑器类型：
		<span class="note">设置各个编辑器类型</span>
	</div>
	<div class="content">
		<table>
		<tr>
			<td><label><input type="radio" name="etype" value="full"<?php if($rs['etype'] == 'full'){ ?> checked<?php } ?> />完整编辑器</label></td>
			<td><label><input type="radio" name="etype" value="simple"<?php if($rs['etype'] == 'simple'){ ?> checked<?php } ?> />精简编辑器</label></td>
		</tr>
		</table>
	</div>
</div>
<div class="table">
	<div class="title">
		编辑器属性：
		<span class="note">设置编辑器常用属性</span>
	</div>
	<div class="content">
		<ul class="layout">
			<li><label><input type="checkbox" name="is_code" id="is_code"<?php if($rs['is_code']){ ?> checked<?php } ?>>显示源码</label></li>
			<li><label><input type="checkbox" name="is_read" id="is_read"<?php if($rs['is_read']){ ?> checked<?php } ?>>只读</label></li>
		</ul>
		<div class="clear"></div>
	</div>
</div>
<div class="table">
	<div class="title">
		编辑器扩展按钮：
		<span class="note"></span>
	</div>
	<div class="content">
		<ul class="layout">
			<li><label><input type="checkbox" name="btn_image" id="btn_image"<?php if($rs['btn_image']){ ?> checked<?php } ?>>图片</label></li>
			<li><label><input type="checkbox" name="btn_info" id="btn_info"<?php if($rs['btn_info']){ ?> checked<?php } ?>>资料</label></li>
			<li><label><input type="checkbox" name="btn_video" id="btn_video"<?php if($rs['btn_image']){ ?> checked<?php } ?>>视频</label></li>
			<li><label><input type="checkbox" name="btn_file" id="btn_file"<?php if($rs['btn_file']){ ?> checked<?php } ?>>文件</label></li>
			<li><label><input type="checkbox" name="btn_page" id="btn_page"<?php if($rs['btn_page']){ ?> checked<?php } ?>>分页</label></li>
			<li><label><input type="checkbox" name="btn_tpl" id="btn_tpl"<?php if($rs['btn_tpl']){ ?> checked<?php } ?>>模板</label></li>
			<li><label><input type="checkbox" name="btn_map" id="btn_map"<?php if($rs['btn_map']){ ?> checked<?php } ?>>百度地图</label></li>
		</ul>
		<div class="clear"></div>
	</div>
</div>