<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><?php $this->output("head","file"); ?>
<script type="text/javascript" src="<?php echo include_js('list.js');?>"></script>
<div class="tips">
	您当前的位置：
	<a href="<?php echo admin_url('list');?>">内容管理</a>
	<?php if($pid){ ?>
		<?php $plist_id["num"] = 0;$plist=is_array($plist) ? $plist : array();$plist_id["total"] = count($plist);$plist_id["index"] = -1;foreach($plist AS $key=>$value){ $plist_id["num"]++;$plist_id["index"]++; ?>
		&raquo; <a href="<?php echo admin_url('list','action');?>&id=<?php echo $value['id'];?>" title="<?php echo $value['title'];?>"><?php echo $value['title'];?></a>
		<?php } ?>
	<?php } ?>	
	<?php if($parent_id){ ?>
	&raquo; 父主题：<a href="<?php echo admin_url('list','edit');?>&id=<?php echo $parent_id;?>" title=""><span class="red"><?php echo $parent_rs['title'];?></span></a>
	<?php } ?>
	&raquo; <?php if($id){ ?>编辑内容<?php } else { ?>添加内容<?php } ?>
</div>
<ul class="group">
	<li class="on" onclick="$.admin.group(this)" name="main" title="基本内容">基本内容</li>
	<li onclick="$.admin.group(this)" name="admin" title="修改发布时间，查看次数及绑定会员等操作">扩展属性</li>
	<li onclick="$.admin.group(this)" name="seo" title="自定义网址，关键字等SEO优化设置">SEO优化</li>
</ul>

<form method="post" id="<?php echo $ext_module;?>" action="<?php echo admin_url('list','ok');?>" onsubmit="return edit_check();">
<input type="hidden" id="id" name="id" value="<?php echo $id;?>" />
<input type="hidden" id="pid" name="pid" value="<?php echo $pid;?>" />
<?php if($parent_id){ ?>
<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id;?>" />
<?php } ?>
<div id="main_setting" class="clearfix" style="postion:relative;">
	<div class="table clearfix">
		<div class="title">
			<?php echo $p_rs['alias_title'] ? $p_rs['alias_title'] : '主题';?>：
			<span class="note"><span class="red">*</span> <?php if($p_rs['alias_note']){ ?><?php echo $p_rs['alias_note'];?>，<?php } ?>此项不为空</span>
		</div>
		<div class="content clearfix">
			<div>
				<table cellpadding="0" cellspacing="0">
				<tr>
					<td><input type="text" id="title" name="title" class="long" value="<?php echo $rs['title'];?>" /></td>
					<?php if($p_rs['cate']){ ?>
					<td>&nbsp;</td>
					<td>
						<select name="cate_id" id="cate_id">
							<option value="0">默认分类</option>
							<?php $catelist_id["num"] = 0;$catelist=is_array($catelist) ? $catelist : array();$catelist_id["total"] = count($catelist);$catelist_id["index"] = -1;foreach($catelist AS $key=>$value){ $catelist_id["num"]++;$catelist_id["index"]++; ?>
							<?php if($cate_popedom == 'all' || $cate_popedom[$value['id']]){ ?>
							<option value="<?php echo $value['id'];?>"<?php if($value['id'] == $rs['cate_id']){ ?> selected<?php } ?>><?php echo $value['_space'];?><?php echo $value['title'];?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</td>
					<?php } ?>
				</tr>
				</table>
			</div>
			<?php if($attrlist && $p_rs['is_attr']){ ?>
			<div>
				<ul class="layout">
					<li>属性：</li>
					<?php $attr = $rs['attr'] ? explode(",",$rs['attr']) : array();?>
					<?php $attrlist_id["num"] = 0;$attrlist=is_array($attrlist) ? $attrlist : array();$attrlist_id["total"] = count($attrlist);$attrlist_id["index"] = -1;foreach($attrlist AS $key=>$value){ $attrlist_id["num"]++;$attrlist_id["index"]++; ?>
					<li><label>
						<table cellpadding="0" cellspacing="0">
						<tr>
							<td><input type="checkbox" name="attr[]" id="_attr_<?php echo $key;?>" value="<?php echo $key;?>"<?php if(in_array($key,$attr)){ ?> checked<?php } ?> /></td>
							<td><?php echo $value;?></td>
						</tr>
					</table>
					</label></li>
					<?php } ?>
					<div class="clear"></div>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>

	<?php if($p_rs['is_biz']){ ?>
	<div class="table clearfix">
		<div class="title">
			价格：
			<span class="note">仅支持数字，免费的请设置为0，倒贴的请写负价格^o^，注意设置相关的货币类型</span>
		</div>
		<div class="content">
			<table cellpadding="0" cellspacing="0">
			<tr>
				<td><input type="text" id="price" name="price" class="default" value="<?php echo $rs['price'];?>" /></td>
				<td>&nbsp;</td>
				<td>
					<select name="currency_id" id="currency_id" onchange="price_show_auto()">
						<?php $currency_list_id["num"] = 0;$currency_list=is_array($currency_list) ? $currency_list : array();$currency_list_id["total"] = count($currency_list);$currency_list_id["index"] = -1;foreach($currency_list AS $key=>$value){ $currency_list_id["num"]++;$currency_list_id["index"]++; ?>
						<option value="<?php echo $value['id'];?>"<?php if($rs['currency_id'] == $value['id']){ ?> selected<?php } ?> code="<?php echo $value['code'];?>" rate="<?php echo $value['val'];?>" sleft="<?php echo $value['symbol_left'];?>" sright="<?php echo $value['symbol_right'];?>"><?php echo $value['title'];?>（标识：<?php echo $value['code'];?>，汇率：<?php echo $value['val'];?>）</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr id="show_price_info_format" class="hide">
				<td>
					<?php $currency_list_id["num"] = 0;$currency_list=is_array($currency_list) ? $currency_list : array();$currency_list_id["total"] = count($currency_list);$currency_list_id["index"] = -1;foreach($currency_list AS $key=>$value){ $currency_list_id["num"]++;$currency_list_id["index"]++; ?>
					<div style="line-height:26px;height:26px;font-size:14px;" class="darkblue"><?php echo $value['title'];?>（<?php echo $value['code'];?>）的价格：<span class="red" id="_show_price_<?php echo $value['code'];?>"></span></div>
					<?php } ?>
				</td>
			</tr>
			</table>
		</div>
	</div>
	<script type="text/javascript">
	function price_show_auto()
	{
		var price = $("#price").val();
		var currency_id = $("#currency_id").val();
		if(!price || price == '0' || !currency_id || price == 'undefined')
		{
			$('#show_price_info_format').hide();
			return false;
		}
		var df = $("#currency_id").find("option:selected").attr("rate");
		var hide_html = false;
		$("#currency_id option").each(function(i){
			var t = $(this).val();
			if(t)
			{
				var code = $(this).attr("code");
				var sleft = $(this).attr("sleft");
				var sright = $(this).attr("sright");
				var rate = $(this).attr("rate");
				//计算
				if(df != rate)
				{
					var price2 = parseFloat((price / df) * rate);
				}
				else
				{
					var price2 = parseFloat(price);
				}
				if(price2.toString() == 'NaN')
				{
					$("#price").val('');
					hide_html = true;
				}
				else
				{
					price2 = price2.toFixed(2);
					price2 = sleft + ' '+(price2).toString()+" "+sright;
					$("#_show_price_"+code).html(price2);
				}
			}
		});
		if(hide_html)
		{
			$("#show_price_info_format").hide();
		}
		else
		{
			$("#show_price_info_format").show();
		}
	}
	$(document).ready(function(){
		price_show_auto();
		$('#price').keyup(function(){price_show_auto();}).keydown(function(){price_show_auto();});
	});
	</script>
	<?php } ?>

	<?php $extlist_id["num"] = 0;$extlist=is_array($extlist) ? $extlist : array();$extlist_id["total"] = count($extlist);$extlist_id["index"] = -1;foreach($extlist AS $key=>$value){ $extlist_id["num"]++;$extlist_id["index"]++; ?>
	<div class="table clearfix">
		<div class="title">
			<?php echo $value['title'];?>：
			<span class="note"><?php echo $value['note'];?></span>
		</div>
		<div class="content"><?php echo $value['html'];?></div>
	</div>
	<?php } ?>
	<?php if($p_rs['is_tag']){ ?>
	<div class="table clearfix">
		<div class="title">
			TAG标签：
			<span class="note">多个TAG用空格分开，一次不能超过10个TAG</span>
		</div>
		<div class="content">
			<input type="text" id="tag" name="tag" class="long" value='<?php echo $rs['tag'];?>' />
		</div>
	</div>
	<?php } ?>
</div>

<div id="admin_setting" class="hide">
	<div class="table">
		<div class="title">
			发布时间：
			<span class="note">人工设置发布时间，不修改默认</span>
		</div>
		<div class="content">
			<input type="text" id="dateline" name="dateline" class="default" value="<?php if($rs['dateline']){ ?><?php echo date('Y-m-d H:i:s',$rs['dateline']);?><?php } else { ?><?php echo date('Y-m-d H:i:s',$app->time);?><?php } ?>" onfocus="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			查看次数：
			<span class="note">可以人工编辑查看次数，此参数用于调用是否热门主题参照物</span>
		</div>
		<div class="content">
			<input type="text" id="hits" name="hits" class="short" value="<?php echo $rs['hits'];?>" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			内容模板：
			<span class="note">此功能可实现内容独立模板，为空将使用 <span class="red"><?php echo $p_rs['tpl_content'];?></span></span>
		</div>
		<div class="content">
			<input type="text" id="tpl" name="tpl" class="default" value="<?php echo $rs['tpl'];?>" />
			<input type="button" value="选择" onclick="phpok_tpl_open('tpl')" class="btn" />
			<input type="button" value="清空" onclick="$('#tpl').val('');" class="btn" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			排序：
			<span class="note">值越大越往前靠，默认为0，支持负数</span>
		</div>
		<div class="content">
			<input type="text" id="sort" name="sort" class="default" value="<?php echo $rs['sort'];?>" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			状态：
			<span class="note">默认为已审核，未审核主题在前台不使用</span>
		</div>
		<div class="content">
			<table>
			<tr>
				<td><label for="status_0"><input type="radio" name="status" id="status_0" value="0"<?php if($id && !$rs['status']){ ?> checked<?php } ?> />未审核</label></td>
				<td><label for="status_1"><input type="radio" name="status" id="status_1" value="1"<?php if(!$id || $rs['status']){ ?> checked<?php } ?> />已审核</label></td>
			</tr>
			</table>
		</div>
	</div>
	<div class="table">
		<div class="title">
			隐藏：
			<span class="note">设为隐藏属性时，在列表中不体现，在调用中也不体现，仅允许直接通过网址访问</span>
		</div>
		<div class="content">
			<table>
			<tr>
				<td><label for="hidden_0"><input type="radio" name="hidden" id="hidden_0" value="0"<?php if(!$rs['hidden']){ ?> checked<?php } ?> />显示</label></td>
				<td><label for="hidden_1"><input type="radio" name="hidden" id="hidden_1" value="1"<?php if($rs['hidden']){ ?> checked<?php } ?> />隐藏</label></td>
			</tr>
			</table>
		</div>
	</div>
	<div class="table">
		<div class="title">
			会员：
			<span class="note">绑定会员ID，该主题将允许某个会员进行维护（前提是会员有此项目的相关权限）</span>
		</div>
		<div class="content">
			<?php echo form_edit('user_id',$rs['user_id'],'user');?>
		</div>
	</div>
</div>
<div id="seo_setting" class="hide">
	<div class="table">
		<div class="title">
			标识：
			<span class="note">限<span class="red">字母、数字、下划线或中划线且必须是字母开头</span>，适用于网址SEO</span>
		</div>
		<div class="content">
			<input type="text" id="identifier" name="identifier" class="default" value="<?php echo $rs['identifier'];?>" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			SEO标题：
			<span class="note">设置此标题后，网站Title将会替代默认定义的，不能超过85个汉字</span>
		</div>
		<div class="content">
			<input type="text" id="seo_title" name="seo_title" class="long" value="<?php echo $rs['seo_title'];?>" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			SEO关键字：
			<span class="note">多个关键字用英文逗号隔开，为空系统将从标题中尝试获取</span>
		</div>
		<div class="content">
			<input type="text" id="seo_keywords" name="seo_keywords" class="long" value="<?php echo $rs['seo_keywords'];?>" />
		</div>
	</div>
	<div class="table">
		<div class="title">
			SEO描述：
			<span class="note">简单描述该主题信息，用于搜索引挈，不支持HTML，不能超过85个汉字</span>
		</div>
		<div class="content">
			<textarea name="seo_desc" id="seo_desc" class="long"><?php echo $rs['seo_desc'];?></textarea>
		</div>
	</div>
	<?php if(!$p_rs['is_tag']){ ?>
	<div class="table">
		<div class="title">
			TAG标签：
			<span class="note">多个TAG用空格分开，一次不能超过10个TAG</span>
		</div>
		<div class="content">
			<input type="text" id="tag" name="tag" class="long" value='<?php echo $rs['tag'];?>' />
		</div>
	</div>
	<?php } ?>
</div>

<div class="table">
	<div class="content">
		<br />
		<input type="submit" value="提 交" class="submit" />
		<br />
	</div>
</div>
</form>
<?php $this->output("foot","file"); ?>