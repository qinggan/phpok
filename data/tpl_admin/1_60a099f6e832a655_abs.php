<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><script id="<?php echo $form_rs['identifier'];?>" type="text/plain" style="<?php echo $form_rs['form_style'];?>"><?php echo $form_rs['content'];?></script>
<?php if($form_rs['etype'] == 'simple'){ ?>
<script type="text/javascript">
var toolbars_<?php echo $form_id;?> = [[
	'fullscreen','source','|', 
	'bold', 'italic', 'underline', 'strikethrough', '|',
	'justifyleft', 'justifycenter', 'justifyright', '|',
	'removeformat', 'autotypeset', 'blockquote', 'pasteplain', '|',
	'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', '|',
	'horizontal', 'date', 'time', 'spechars', '|',
	'inserttable', 'link', 'unlink','|',
	<?php if($form_rs['btn_map']){ ?>'map',<?php } ?>
	<?php if($form_rs['btn_image']){ ?>'insertimage',<?php } ?>
	<?php if($form_rs['btn_video']){ ?>'insertvideo',<?php } ?>
	<?php if($form_rs['btn_file']){ ?>'attachment',<?php } ?>
	<?php if($form_rs['btn_info']){ ?>'phpokinfo',<?php } ?>
	<?php if($form_rs['btn_image'] || $form_rs['btn_video'] || $form_rs['btn_file'] || $form_rs['btn_info']){ ?>'|',<?php } ?>
	'help'
]];
</script>
<?php } else { ?>
<script type="text/javascript">
var toolbars_<?php echo $form_id;?> = [[
	'fullscreen','source','|','undo', 'redo', '|',
	'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript','|',
	'justifyleft', 'justifycenter', 'justifyright', '|',
	'removeformat', 'autotypeset', 'blockquote', 'pasteplain', '|',
	'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'lineheight', '|',
	'touppercase', 'tolowercase','inserttable','horizontal','|',
	'link', 'unlink', 'anchor',
	<?php if($form_rs['btn_image']){ ?>'|','insertimage',<?php } ?>
	'paragraph', 'fontfamily', 'fontsize','insertcode',
	'indent','|',
	'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
	<?php if($form_rs['btn_map']){ ?>'map',<?php } ?>
	<?php if($form_rs['btn_page']){ ?>'pagebreak',<?php } ?>
	<?php if($form_rs['btn_tpl']){ ?>'template',<?php } ?>
	'date', 'time', 'spechars',
	'wordimage', '|',
	<?php if($form_rs['btn_video']){ ?>'insertvideo',<?php } ?>
	<?php if($form_rs['btn_file']){ ?>'attachment',<?php } ?>
	<?php if($form_rs['btn_info']){ ?>'phpokinfo',<?php } ?>
	<?php if($form_rs['btn_video'] || $form_rs['btn_file'] || $form_rs['btn_info']){ ?>'|',<?php } ?>
	'background','preview', 'searchreplace','help'
]];
</script>
<?php } ?>
<script type="text/javascript">
var edit_config_<?php echo $form_id;?> = {
	 'UEDITOR_HOME_URL':webroot+'js/ueditor/'
	,'serverUrl':get_url('ueditor')
	,'toolbars':toolbars_<?php echo $form_id;?>
	//扩展字段
	,'labelMap':{'phpokinfo':'主题库'}
    //初始化宽度
    ,'initialFrameWidth':'<?php echo $form_rs['width'];?>'
    //允许使用DIV
    ,'allowDivTransTop': false
    //初始化高度
    ,'initialFrameHeight':'<?php echo $form_rs['height'];?>'
	,'sourceEditorFirst':<?php echo $form_rs['is_code'] ? 'true' :'false';?>
	,'readonly':<?php echo $form_rs['is_read'] ? 'true' :'false';?>
	,'autoFloatEnabled':false
	,'autoHeightEnabled':false
	,'pageBreakTag':'[:page:]'
	,'textarea':'<?php echo $form_rs['identifier'];?>'
	,'iframeUrlMap':{
		'phpokinfo':get_url('ueditor','info')
	}
};
var edit_<?php echo $form_id;?> = UE.getEditor('<?php echo $form_rs['identifier'];?>',edit_config_<?php echo $form_id;?>);
</script>
