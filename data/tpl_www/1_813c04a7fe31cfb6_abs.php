<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>友情提示</title>
<meta name="author" content="phpok.com" />
<link href="css/tips.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo admin_url('js');?>"></script>
</head>
<body>
<div class="body" style="width:400px;border:0;margin:30px;">
	<div class="tips <?php echo $type;?>">
		<?php if($btn){ ?>
		<div class="title"><?php echo $tips;?></div>
		<div class="note"><?php echo $btn;?></div>
		<?php } else { ?>
		<div class="txt" style="line-height:45px;"><?php echo $tips;?></div>
		<?php } ?>
	</div>
</div>
</body>
</html>
