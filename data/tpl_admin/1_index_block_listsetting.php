<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><script type="text/javascript">
function list_refresh()
{
	var url = get_url('index','list_setting');
	var rs = $.phpok.json(url);
	if(rs.status == 'ok')
	{
		$("#list_setting").html(rs.content).show();
	}
	else
	{
		$("#list_setting").html('').hide();
	}
}
</script>
<div class="title">
	<span class="maintain">内容管理</span>
	<a href="javascript:list_refresh();void(0);" class="refresh">刷新</a>
</div>
<div class="box_item">
	<ul>
		<?php $list_rslist_id["num"] = 0;$list_rslist=is_array($list_rslist) ? $list_rslist : array();$list_rslist_id["total"] = count($list_rslist);$list_rslist_id["index"] = -1;foreach($list_rslist AS $key=>$value){ $list_rslist_id["num"]++;$list_rslist_id["index"]++; ?>
		<li><a title="<?php echo $value['title'];?>" href="javascript:$.win('<?php echo $value['title'];?>','<?php echo phpok_url(array('ctrl'=>'list','func'=>'action','id'=>$value['id']));?>');void(0);">
			<div class="top_img"><img src="<?php echo $value['ico'] ? $value['ico'] : 'images/ico/default.png';?>" class="ie6png" alt="<?php echo $value['title'];?>" width="48" height="48" /></div>
			<div class="name"><?php echo $value['title'];?></div></a>
		</li>
		<?php } ?>
	</ul>
</div>