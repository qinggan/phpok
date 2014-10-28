<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $this->output("head","file"); ?>
<script type="text/javascript" src="<?php echo add_js('module.js');?>"></script>
<div class="tips">
	<?php if($popedom['set']){ ?>
	<div class="action"><a href="<?php echo admin_url('module','set');?>">添加模块</a></div>
	<?php } ?>
	您当前的位置：
	<a href="<?php echo admin_url('module');?>">模块管理</a>
	&raquo; 模块列表<span class="i">(<?php echo count($rslist);?>)</span>
</div>
<div class="list">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th class="id">&nbsp;</th>
	<th width="30px">&nbsp;</th>
	<th width="50px">ID</th>
	<th class="lft">名称</th>
	<th>排序</th>
	<th class="action">操作</th>
</tr>
<?php $rslist_id["num"] = 0;$rslist=is_array($rslist) ? $rslist : array();$rslist_id["total"] = count($rslist);$rslist_id["index"] = -1;foreach($rslist AS $key=>$value){ $rslist_id["num"]++;$rslist_id["index"]++; ?>
<tr>
	<td class="id"><input type="checkbox" value="<?php echo $value['id'];?>" id="tid_<?php echo $value['id'];?>" checked /></td>
	<td><span class="status<?php echo $value['status'];?>" id="status_<?php echo $value['id'];?>" <?php if($popedom['set']){ ?>onclick="set_status(<?php echo $value['id'];?>)"<?php } else { ?> style="cursor:default"<?php } ?> value="<?php echo $value['status'];?>"></span></td>
	<td align="center"><?php echo $value['id'];?></td>
	<td><label for="tid_<?php echo $value['id'];?>"><?php echo $value['title'];?><?php if($value['note']){ ?><span class="gray i">（<?php echo $value['note'];?>）</span><?php } ?></label></td>
	<td class="center"><input type="text" id="taxis_<?php echo $value['id'];?>" class="short center" value="<?php echo $value['taxis'];?>" /></td>
	<td>
		<?php if($popedom['set']){ ?>
		<a class="icon edit" href="<?php echo admin_url('module','set');?>&id=<?php echo $value['id'];?>" title="修改"></a>
		<a class="icon field" href="<?php echo admin_url('module','fields');?>&id=<?php echo $value['id'];?>" title="字段管理器"></a>
		<a class="icon copy" onclick="module_copy('<?php echo $value['id'];?>','<?php echo $value['title'];?>')" title="复制模块"></a>
		<a class="icon layout" onclick="module_layout('<?php echo $value['id'];?>','<?php echo $value['title'];?>')" title="后台列表字段显示"></a>
		<a class="icon delete end" onclick="module_del('<?php echo $value['id'];?>','<?php echo $value['title'];?>')" title="删除"></a>
		<?php } ?>
	</td>
</tr>
<?php } ?>
</table>
</div>
<?php if($rslist){ ?>
<div>
<table>
<tr>
	<td><input type="button" value="全选" class="btn" onclick="$.input.checkbox_all()" /></td>
	<td><input type="button" value="全不选" class="btn" onclick="$.input.checkbox_none()" /></td>
	<td><input type="button" value="反选" class="btn" onclick="$.input.checkbox_anti()" /></td>
	<td>&nbsp; &raquo; &nbsp;</td>
	<td><input type="button" value="更新排序" class="btn" onclick="taxis('<?php echo admin_url('module','taxis');?>','255')" /></td>
</tr>
</table>
</div>
<?php } ?>
<?php $this->output("foot","file"); ?>
