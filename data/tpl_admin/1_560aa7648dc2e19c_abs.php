<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $title="数据库比较结果报表";?><?php $this->assign("title","数据库比较结果报表"); ?><?php $this->output("head","file"); ?>
<div class="tips">主数据库：<span class="red"><?php echo $data1;?></span> 与副数据库：<span class="red"><?php echo $data2;?></span> 差异结果集</div>
<div class="sqlmain">
<table cellspacing="0" cellpadding="0" class="sqlframe">
<tr>
	<th width="50%"><?php echo $data1;?></th>
	<th><?php echo $data2;?></th>
</tr>
<?php $rslist_id["num"] = 0;$rslist=is_array($rslist) ? $rslist : array();$rslist_id["total"] = count($rslist);$rslist_id["index"] = -1;foreach($rslist AS $key=>$value){ $rslist_id["num"]++;$rslist_id["index"]++; ?>
<tr>
	<td valign="top">
		<table class="sublist" cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<th colspan="5" class="tit"><?php echo $key;?></th>
		</tr>
		<?php if(!$value['one'] && $value['error'] && is_string($value['error'])){ ?>
		<tr>
			<td colspan="5" class="error"><?php echo $value['error'];?></td>
		</tr>
		<?php } else { ?>
			<tr>
				<th width="20%">Field</th>
				<th width="20%">Type</th>
				<th width="20%">Null</th>
				<th width="20%">Key</th>
				<th width="20%">Default</th>
			</tr>
			<?php $value_one_id["num"] = 0;$value['one']=is_array($value['one']) ? $value['one'] : array();$value_one_id["total"] = count($value['one']);$value_one_id["index"] = -1;foreach($value['one'] AS $k=>$v){ $value_one_id["num"]++;$value_one_id["index"]++; ?>
			<?php if($v['error']){ ?>
			<tr>
				<td class="error" colspan="5"><?php echo $v['error'];?></td>
			</tr>
			<?php } else { ?>
			<tr<?php if($value['error'][$k]){ ?> class="error"<?php } ?>>
				<td><?php echo $v['field'];?></td>
				<td><?php echo $v['type'];?></td>
				<td><?php echo $v['null'] != '' ? $v['null'] : '&nbsp;';?></td>
				<td><?php echo $v['key'] != '' ? $v['key'] : '&nbsp;';?></td>
				<td><?php echo $v['default'] != '' ? $v['default'] : '&nbsp;';?></td>
			</tr>
			<?php } ?>
			<?php } ?>
		<?php } ?>
		</table>
	</td>
	<td valign="top">
		<table class="sublist" cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<th colspan="5" class="tit"><?php echo $key;?></th>
		</tr>
		<?php if(!$value['two'] && $value['error'] && is_string($value['error'])){ ?>
		<tr>
			<td colspan="5" class="error"><?php echo $value['error'];?></td>
		</tr>
		<?php } else { ?>
			<tr>
				<th width="20%">Field</th>
				<th width="20%">Type</th>
				<th width="20%">Null</th>
				<th width="20%">Key</th>
				<th width="20%">Default</th>
			</tr>
			<?php $value_two_id["num"] = 0;$value['two']=is_array($value['two']) ? $value['two'] : array();$value_two_id["total"] = count($value['two']);$value_two_id["index"] = -1;foreach($value['two'] AS $k=>$v){ $value_two_id["num"]++;$value_two_id["index"]++; ?>
			<?php if($v['error']){ ?>
			<tr>
				<td class="error" colspan="5"><?php echo $v['error'];?></td>
			</tr>
			<?php } else { ?>
			<tr<?php if($value['error'][$k]){ ?> class="error"<?php } ?>>
				<td><?php echo $v['field'];?></td>
				<td><?php echo $v['type'];?></td>
				<td><?php echo $v['null'] != '' ? $v['null'] : '&nbsp;';?></td>
				<td><?php echo $v['key'] != '' ? $v['key'] : '&nbsp;';?></td>
				<td><?php echo $v['default'] != '' ? $v['default'] : '&nbsp;';?></td>
			</tr>
			<?php } ?>
			<?php } ?>
		<?php } ?>
		</table>
	</td>
</tr>
<?php } ?>
</table>
</div>
<?php $this->output("foot","file"); ?>