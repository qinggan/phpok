<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $this->output("head","file"); ?>
<script type="text/javascript" src="<?php echo include_js('list.js');?>"></script>
<?php if($project_list){ ?>
<script type="text/javascript">
$(document).ready(function(){
	$("#project li").mouseover(function(){
		$(this).addClass("hover");
	}).mouseout(function(){
		$(this).removeClass("hover");
	}).click(function(){
		var url = $(this).attr("href");
		if(url)
		{
			direct(url);
		}
		else
		{
			alert("未指定动作！");
			return false;
		}
	});
	
});
</script>
<div class="tips"><span class="red"><?php echo $rs['title'];?></span> 子项信息，请点击进入修改</div>
<ul class="project" id="project">
	<?php $project_list_id["num"] = 0;$project_list=is_array($project_list) ? $project_list : array();$project_list_id["total"] = count($project_list);$project_list_id["index"] = -1;foreach($project_list AS $key=>$value){ $project_list_id["num"]++;$project_list_id["index"]++; ?>
	<li id="project_<?php echo $value['id'];?>" title="<?php echo $value['title'];?>" status="<?php echo $value['status'];?>" href="<?php echo phpok_url(array('ctrl'=>'list','func'=>'action','id'=>$value['id']));?>">
		<div class="img"><img src="<?php echo $value['ico'] ? $value['ico'] : 'images/ico/default.png';?>" /></div>
		<div class="txt" id="txt_<?php echo $value['id'];?>"><?php echo $value['nick_title'] ? $value['nick_title'] : $value['title'];?></div>
	</li>
	<?php } ?>
</ul>
<div class="clear"></div>
<?php } ?>

<?php if($rs['module']){ ?>
<script type="text/javascript">
function list_content_search()
{
	$.dialog({
		'title':'搜索',
		'content':document.getElementById("top_search_html"),
		'ok':function(){
			var url = get_url("list",'action','id=<?php echo $pid;?>');
			var attr = $("#search_attr").val();
			if(attr)
			{
				url += "&attr="+$.str.encode(attr);
			}
			var keywords = $("#keywords").val();
			if(keywords)
			{
				url += '&keywords='+$.str.encode(keywords);
			}
			var cateid = $("#cateid").val();
			if(cateid)
			{
				url += '&cateid='+cateid;
			}
			if(!cateid && !keywords && !attr)
			{
				$.dialog.alert('请输入要搜索的关键字或属性');
				return false;
			}
			else
			{
				$.phpok.go(url);
			}
			return true;
		},
		'okVal':'执行搜索',
		'lock':true,
		'drag':false
	});
}
</script>
<div class="tips">
	您当前的位置：<a href="<?php echo admin_url('list');?>" title="内容管理">内容管理</a>
	<?php $plist_id["num"] = 0;$plist=is_array($plist) ? $plist : array();$plist_id["total"] = count($plist);$plist_id["index"] = -1;foreach($plist AS $key=>$value){ $plist_id["num"]++;$plist_id["index"]++; ?>
	&raquo; <a href="<?php echo admin_url('list','action');?>&id=<?php echo $value['id'];?>" title="<?php echo $value['title'];?>"><?php echo $value['title'];?></a>
	<?php } ?>
	<?php if($show_parent_catelist){ ?>
	&raquo; <a href="<?php echo phpok_url(array('ctrl'=>'list','func'=>'action','id'=>$pid,'cateid'=>$show_parent_catelist));?>"><?php echo $parent_cate_rs['title'];?></a>
	<?php } ?>
	&raquo; 内容管理
	<?php if($keywords){ ?>
	&raquo; <span class="red"><?php echo $keywords;?></span>
	<?php } ?>
	<?php if($popedom['set'] || $session['admin_rs']['if_system']){ ?>
	<div class="action"><a href="<?php echo admin_url('list','set');?>&id=<?php echo $pid;?>">编辑项目</a></div>
	<?php } ?>
	<?php if($popedom['add']){ ?>
	<div class="action"><a href="<?php echo admin_url('list','edit');?>&pid=<?php echo $pid;?>">添加内容</a></div>
	<?php } ?>
	<div class="action"><a href="javascript:list_content_search();void(0);">搜索</a></div>
	<span id="AP_ACTION_HTML" title="插件节点，后台内容列表管理栏面包屑"></span>
</div>
<div style="display:none" id="top_search_html">
	<div class="table">
		<div class="title">
			属&nbsp; 性：
			<select name="attr" id="search_attr">
				<option value="">属性选择</option>
				<?php $attrlist_id["num"] = 0;$attrlist=is_array($attrlist) ? $attrlist : array();$attrlist_id["total"] = count($attrlist);$attrlist_id["index"] = -1;foreach($attrlist AS $key=>$value){ $attrlist_id["num"]++;$attrlist_id["index"]++; ?>
				<option value="<?php echo $key;?>"<?php if($attr == $key){ ?> selected<?php } ?>><?php echo $value;?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<?php if($catelist){ ?>
	<div class="table">
		<div class="title">
			分&nbsp; 类：
			<select name="top_cate_id" id="top_cate_id">
				<option value="">全部分类</option>
				<?php $catelist_id["num"] = 0;$catelist=is_array($catelist) ? $catelist : array();$catelist_id["total"] = count($catelist);$catelist_id["index"] = -1;foreach($catelist AS $key=>$value){ $catelist_id["num"]++;$catelist_id["index"]++; ?>
				<option value="<?php echo $value['id'];?>"<?php if($cateid == $value['id']){ ?> selected<?php } ?>><?php echo $value['title'];?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<?php } ?>
	<div class="table">
		<div class="title">
			关键字：<input type="text" id="keywords" name="keywords" class="default" value="<?php echo $keywords;?>" />
		</div>
	</div>
</div>
<?php } ?>

<?php if($rs['admin_note']){ ?>
<div class="tips clearfix"><?php echo $rs['admin_note'];?></div>
<?php } ?>


<?php if($catelist){ ?>
<ul class="list_cate clearfix">
	<?php $catelist_id["num"] = 0;$catelist=is_array($catelist) ? $catelist : array();$catelist_id["total"] = count($catelist);$catelist_id["index"] = -1;foreach($catelist AS $key=>$value){ $catelist_id["num"]++;$catelist_id["index"]++; ?>
	<?php if($cate_popedom == 'all' || $cate_popedom[$value['id']]){ ?>
	<li>
		<div class="cate cate_<?php echo $catelist_id['num']%9;?>"><a href="<?php echo admin_url('list','action');?>&id=<?php echo $pid;?>&cateid=<?php echo $value['id'];?>"><?php echo $value['title'];?></a></div>
		<div class="cate_add cate_<?php echo $catelist_id['num']%9;?>"><a href="<?php echo admin_url('list','edit');?>&pid=<?php echo $pid;?>&cateid=<?php echo $value['id'];?>" title="添加《<?php echo $value['title'];?>》下的 【<?php echo $rs['title'];?>】"><img src="images/cate_add.png" border="0" alt="" /></a></div>
	</li>
	<?php } ?>
	<?php } ?>
</ul>
<div class="clear"></div>
<?php } ?>

<?php if($rslist){ ?>
<div class="list clearfix">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th width="20px">&nbsp;</th>
	<th width="20px">&nbsp;</th>
	<th class="lft"><?php echo $rs['alias_title'] ? $rs['alias_title'] : '主题';?></th>
	<?php if($rs['cate']){ ?>
	<th>分类</th>
	<?php } ?>
	<?php $layout_id["num"] = 0;$layout=is_array($layout) ? $layout : array();$layout_id["total"] = count($layout);$layout_id["index"] = -1;foreach($layout AS $key=>$value){ $layout_id["num"]++;$layout_id["index"]++; ?>
		<?php if($key == "dateline"){ ?>
		<th style="width:150px"><?php echo $value;?></th>
		<?php }elseif($key == "hits"){ ?>
		<th style="width:50px">点击</th>
		<?php } else { ?>
		<th class="lft"><?php echo $value;?></th>
		<?php } ?>
	<?php } ?>
	<th style="width:90px">排序</th>
	<th style="width:<?php echo $rs['subtopics'] ? '90px' : '60px';?>">操作</th>
</tr>
<?php $tmp_m = 0;?>
<?php $rslist_id["num"] = 0;$rslist=is_array($rslist) ? $rslist : array();$rslist_id["total"] = count($rslist);$rslist_id["index"] = -1;foreach($rslist AS $key=>$value){ $rslist_id["num"]++;$rslist_id["index"]++; ?>
<?php $tmp_m++;?>
<tr id="list_<?php echo $value['id'];?>" title="<?php echo $rs['alias_title'] ? $rs['alias_title'] : '主题';?>：<?php echo $value['title'];?>&#10;发布日期：<?php echo date('Y-m-d H:i:s',$value['dateline']);?>">
	<td class="center"><input type="checkbox" name="ids[]" id="id_<?php echo $value['id'];?>" value="<?php echo $value['id'];?>" /></td>
	<td><span class="status<?php echo $value['status'];?>" id="status_<?php echo $value['id'];?>" <?php if($popedom['status']){ ?>onclick="set_status(<?php echo $value['id'];?>)"<?php } else { ?> style="cursor: default;"<?php } ?> value="<?php echo $value['status'];?>"></span></td>
	<td><label for="id_<?php echo $value['id'];?>">
		<?php echo $value['id'];?>. <?php echo $value['title'];?>
		<?php if($value['attr']){ ?>
			<?php $attr = explode(",",$value['attr']);?>
			<?php $attr_id["num"] = 0;$attr=is_array($attr) ? $attr : array();$attr_id["total"] = count($attr);$attr_id["index"] = -1;foreach($attr AS $attr_k=>$attr_v){ $attr_id["num"]++;$attr_id["index"]++; ?>
			<a href="<?php echo admin_url('list','action');?>&id=<?php echo $pid;?>&attr=<?php echo $attr_v;?>" class="gray">[<?php echo $attrlist[$attr_v];?>]</a>
			<?php } ?>
		<?php } ?>
		<?php if($value['identifier']){ ?>
		<span class="gray i">（<?php echo $value['identifier'];?>）</span>
		<?php } ?>
		<?php if($rs['is_biz']){ ?>
		<span class="red i"> <?php echo price_format($value['price'],$value['currency_id']);?></span>
		<?php } ?>
		<?php if($value['hidden']){ ?>
		<span class="red i">(隐藏)</span>
		<?php } ?>
	</label>
	</td>
	<?php if($rs['cate']){ ?>
	<td class="gray center">
		<?php if($value['cate_id'] && is_array($value['cate_id'])){ ?>
		<a href="<?php echo admin_url('list','action');?>&id=<?php echo $pid;?>&cateid=<?php echo $value['cate_id']['id'];?>"><?php echo $value['cate_id']['title'];?></a>
		<?php } else { ?>
		未设分类
		<?php } ?>
	</td>
	<?php } ?>
	
	<?php $layout_id["num"] = 0;$layout=is_array($layout) ? $layout : array();$layout_id["total"] = count($layout);$layout_id["index"] = -1;foreach($layout AS $k=>$v){ $layout_id["num"]++;$layout_id["index"]++; ?>
		<?php if($k == "dateline"){ ?>
		<td class="center"><?php echo date('Y-m-d',$value['dateline']);?></td>
		<?php }elseif($k == "hits"){ ?>
		<td class="center"><?php echo $value['hits'];?></td>
		<?php } else { ?>
			<?php if(is_array($value[$k])){ ?>
				<?php $c_list = $value[$k]['_admin'];?>
				<?php if($c_list['type'] == 'pic'){ ?>
				<td><img src="<?php echo $c_list['info'];?>" width="28px" height="28px" border="0" class="hand" onclick="preview_attr('<?php echo $c_list['id'];?>')" style="border:1px solid #dedede;padding:1px;" /></td>
				<?php } else { ?>
					<?php if(is_array($c_list['info'])){ ?>
					<td><?php echo implode(' / ',$c_list['info']);?></td>
					<?php } else { ?>
					<td><?php echo $c_list['info'] ? $c_list['info'] : '-';?></td>
					<?php } ?>
				<?php } ?>
			<?php } else { ?>
			<td><?php echo $value[$k] ? $value[$k] : '-';?></td>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	<td class="center"><input type="text" id="sort_<?php echo $value['id'];?>" name="sort[]" class="short center" value="<?php echo $value['sort'];?>" tabindex="<?php echo $tmp_m;?>" /></td>
	<td>
		<?php if($rs['subtopics'] && !$value['parent_id'] && $popedom['add']){ ?>
		<a class="icon add" href="<?php echo admin_url('list','edit');?>&parent_id=<?php echo $value['id'];?>&pid=<?php echo $value['project_id'];?>" title="添加子主题"></a>
		<?php } ?>
		<?php if($popedom['modify']){ ?>
		<a class="icon edit" href="<?php echo admin_url('list','edit');?>&id=<?php echo $value['id'];?>" title="修改"></a>
		<?php } ?>
		<?php if($popedom['delete']){ ?>
		<a class="icon delete end" onclick="content_del('<?php echo $value['id'];?>')" title="删除"></a>
		<?php } ?>
	</td>
</tr>
	<?php $value_sonlist_id["num"] = 0;$value['sonlist']=is_array($value['sonlist']) ? $value['sonlist'] : array();$value_sonlist_id["total"] = count($value['sonlist']);$value_sonlist_id["index"] = -1;foreach($value['sonlist'] AS $kk=>$vv){ $value_sonlist_id["num"]++;$value_sonlist_id["index"]++; ?>
	<?php $tmp_m++;?>
	<tr id="list_<?php echo $vv['id'];?>" title="<?php echo $rs['alias_title'] ? $rs['alias_title'] : '主题';?>：<?php echo $vv['title'];?>&#10;发布日期：<?php echo date('Y-m-d H:i:s',$vv['dateline']);?>">
		<td class="center"><input type="checkbox" name="ids[]" id="id_<?php echo $vv['id'];?>" value="<?php echo $vv['id'];?>" /></td>
		<td><span class="status<?php echo $vv['status'];?>" id="status_<?php echo $vv['id'];?>" <?php if($popedom['status']){ ?>onclick="set_status(<?php echo $vv['id'];?>)"<?php } else { ?> style="cursor: default;"<?php } ?> value="<?php echo $vv['status'];?>"></span></td>
		<td><label for="id_<?php echo $vv['id'];?>">
			&nbsp; &nbsp; ├─ <?php echo $vv['title'];?>
			<?php if($vv['attr']){ ?>
				<?php $attr = explode(",",$vv['attr']);?>
				<?php $attr_id["num"] = 0;$attr=is_array($attr) ? $attr : array();$attr_id["total"] = count($attr);$attr_id["index"] = -1;foreach($attr AS $attr_k=>$attr_v){ $attr_id["num"]++;$attr_id["index"]++; ?>
				[<?php echo $attrlist[$attr_v];?>]
				<?php } ?>
			<?php } ?>
			<?php if($vv['identifier']){ ?>
			<span class="gray i">（<?php echo $vv['identifier'];?>）</span>
			<?php } ?>
			<?php if($rs['is_biz']){ ?>
			<span class="red i"> <?php echo price_format($vv['price'],$vv['currency_id']);?></span>
			<?php } ?>
			<?php if($vv['hidden']){ ?>
			<span class="red i">(隐藏)</span>
			<?php } ?>
		</label>
		</td>
		<?php if($rs['cate']){ ?>
		<td class="gray center">
			<?php if($vv['cate_id'] && is_array($vv['cate_id'])){ ?>
			<a href="<?php echo admin_url('list','action');?>&id=<?php echo $pid;?>&cateid=<?php echo $vv['cate_id']['id'];?>"><?php echo $vv['cate_id']['title'];?></a>
			<?php } else { ?>
			未设分类
			<?php } ?>
		<?php } ?>
		<?php $layout_id["num"] = 0;$layout=is_array($layout) ? $layout : array();$layout_id["total"] = count($layout);$layout_id["index"] = -1;foreach($layout AS $k=>$v){ $layout_id["num"]++;$layout_id["index"]++; ?>
			<?php if($k == "dateline"){ ?>
			<td class="center"><?php echo date("Y-m-d",$vv['dateline']);?></td>
			<?php }elseif($k == "hits"){ ?>
			<td class="center"><?php echo $vv['hits'];?></td>
			<?php } else { ?>
				<?php if(is_array($vv[$k])){ ?>
					<?php $c_list = $vv[$k]['_admin'];?>
					<?php if($c_list['type'] == 'pic'){ ?>
					<td><img src="<?php echo $c_list['info'];?>" width="28px" height="28px" border="0" class="hand" onclick="preview_attr('<?php echo $c_list['id'];?>')" style="border:1px solid #dedede;padding:1px;" /></td>
					<?php } else { ?>
						<?php if(is_array($c_list['info'])){ ?>
						<td><?php echo implode(' / ',$c_list['info']);?></td>
						<?php } else { ?>
						<td><?php echo $c_list['info'] ? $c_list['info'] : '-';?></td>
						<?php } ?>
					<?php } ?>
				<?php } else { ?>
				<td><?php echo $vv[$k] ? $vv[$k] : '-';?></td>
				<?php } ?>
			<?php } ?>
		<?php } ?>
		<td class="center"><input type="text" id="sort_<?php echo $vv['id'];?>" name="sort[]" class="short center" value="<?php echo $vv['sort'];?>" tabindex="<?php echo $tmp_m;?>" /></td>
		<td>
			<a class="icon space"></a>
			<?php if($popedom['modify']){ ?>
			<a class="icon edit" href="<?php echo admin_url('list','edit');?>&id=<?php echo $vv['id'];?>" title="修改"></a>
			<?php } ?>
			<?php if($popedom['delete']){ ?>
			<a class="icon delete end" onclick="content_del('<?php echo $vv['id'];?>')" title="删除"></a>
			<?php } ?>
		</td>
	</tr>
	<?php } ?>
<?php } ?>
</table>
</div>
<?php } ?>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<td>
		<?php if($rslist){ ?>
		<ul class="layout">
			<li><input type="button" value="全选" class="btn" onclick="$.input.checkbox_all()" /></li>
			<li><input type="button" value="全不选" class="btn" onclick="$.input.checkbox_none()" /></li>
			<li><input type="button" value="反选" class="btn" onclick="$.input.checkbox_anti()" /></li>
			<li><select id="list_action_val" style="width:200px;margin-top:0px;padding:1px;">
				<option value="">选择要执行的动作…</option>
				<?php if($opt_catelist){ ?>
				<optgroup label="移动分类">
					<?php $opt_catelist_id["num"] = 0;$opt_catelist=is_array($opt_catelist) ? $opt_catelist : array();$opt_catelist_id["total"] = count($opt_catelist);$opt_catelist_id["index"] = -1;foreach($opt_catelist AS $key=>$value){ $opt_catelist_id["num"]++;$opt_catelist_id["index"]++; ?>
					<?php if($cate_popedom == 'all' || $cate_popedom[$value['id']]){ ?>
					<option value="<?php echo $value['id'];?>"<?php if($value['id'] == $rs['cate_id']){ ?> selected<?php } ?>><?php echo $value['_space'];?><?php echo $value['title'];?></option>
					<?php } ?>
					<?php } ?>
				</optgroup>
				<?php } ?>
				<optgroup label="添加属性">
					<?php $attrlist_id["num"] = 0;$attrlist=is_array($attrlist) ? $attrlist : array();$attrlist_id["total"] = count($attrlist);$attrlist_id["index"] = -1;foreach($attrlist AS $key=>$value){ $attrlist_id["num"]++;$attrlist_id["index"]++; ?>
					<option value="add:<?php echo $key;?>"><?php echo $value;?></option>
					<?php } ?>
				</optgroup>
				<optgroup label="移除属性">
					<?php $attrlist_id["num"] = 0;$attrlist=is_array($attrlist) ? $attrlist : array();$attrlist_id["total"] = count($attrlist);$attrlist_id["index"] = -1;foreach($attrlist AS $key=>$value){ $attrlist_id["num"]++;$attrlist_id["index"]++; ?>
					<option value="delete:<?php echo $key;?>"><?php echo $value;?></option>
					<?php } ?>
				</optgroup>
				<optgroup label="其他">
					<?php if($popedom['status']){ ?>
					<option value="status">批量审核</option>
					<option value="unstatus">批量取消审核</option>
					<?php } ?>
					<option value="hidden">批量隐藏</option>
					<option value="show">批量显示</option>
					<?php if($popedom['delete']){ ?>
					<option value="delete">批量删除</option>
					<?php } ?>
					<option value="sort">批量排序</option>
				</optgroup>
				</select></li>
			<li id="plugin_button"><input type="button" value="执行操作" onclick="list_action_exec()" class="submit" /></li>
			<li><input type="button" value="批处理" onclick="direct('<?php echo phpok_url(array('ctrl'=>'list','func'=>'plaction','id'=>$pid));?>')" class="btn" /></li>
		</ul>
		<?php } ?>
	</td>
	<td align="right"><?php $this->output("pagelist","file"); ?></td>
</tr>
</table>


<?php $this->output("foot","file"); ?>