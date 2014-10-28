<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php if($pagelist){ ?>
<div class="pagelist">
	<ul>
		<?php $pagelist_id["num"] = 0;$pagelist=is_array($pagelist) ? $pagelist : array();$pagelist_id["total"] = count($pagelist);$pagelist_id["index"] = -1;foreach($pagelist AS $key=>$value){ $pagelist_id["num"]++;$pagelist_id["index"]++; ?>
			<?php if($value['type'] != 'opt'){ ?>
			<li><?php if($value['url']){ ?><a href="<?php echo $value['url'];?>"<?php if($value['status']){ ?> class="current"<?php } ?>><?php echo $value['title'];?></a><?php } else { ?><a><?php echo $value['title'];?></a><?php } ?></li>
			<?php } ?>
			<?php if($value['type'] == 'opt'){ ?>
			<li>
				<select onchange="direct('<?php echo $value['url'];?>'+this.value)">
					<?php $value_title_id["num"] = 0;$value['title']=is_array($value['title']) ? $value['title'] : array();$value_title_id["total"] = count($value['title']);$value_title_id["index"] = -1;foreach($value['title'] AS $k=>$v){ $value_title_id["num"]++;$value_title_id["index"]++; ?>
					<option value="<?php echo $v['value'];?>"<?php if($v['status']){ ?> selected<?php } ?>><?php echo $v['title'];?></option>
					<?php } ?>
				</select>
			</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
<?php } ?>