<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>友情提示</title>
<?php if($sys['url']){ ?><base href="<?php echo $sys['url'];?>" /><?php } ?>
<meta name="author" content="phpok.com" />
<link href="css/tips.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="body">
	<div class="tips <?php echo $type;?>">
		<?php if($url){ ?>
		<div class="title"><?php echo $tips;?></div>
		<div class="note"><a href="<?php echo $url;?>">系统将在 <span class="red"><?php echo $time;?>秒</span> 后跳转，手动跳转请点这里</a></div>
		<?php } else { ?>
		<div class="txt" style="line-height:52px;"><?php echo $tips;?></div>
		<?php } ?>
	</div>
</div>
<?php if($url){ ?>
	<script type="text/javascript">
	var url = "<?php echo $url;?>";
	var mtime = <?php echo $time;?> * 1000;
	function refresh()
	{
		url = url.replace(/&amp;/g,"&");
		window.location.href = url;
	}
	window.setTimeout("refresh()",mtime);
	</script>
<?php } ?>
</body>
</html>
