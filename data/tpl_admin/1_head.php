<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="author" content="phpok.com" />
	<meta http-equiv="Expires" content="wed, 26 feb 1997 08:21:57 GMT" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php if($title){ ?><?php echo $title;?> - <?php } ?><?php echo $site_info['title'] ? $site_info['title'] : $config['title'];?> - 后台管理</title>
	<link rel="stylesheet" type="text/css" href="css/admin.css?version=<?php echo VERSION;?>" rel="stylesheet" type="text/css" />
	<?php echo phpok_head_css();?>
	<script type="text/javascript" src="<?php echo phpok_url(array('ctrl'=>'js'));?>"></script>
	<?php echo phpok_head_js();?>
	<?php echo $GLOBALS["app"]->plugin_html_ap("head");?>
</head>
<body>
<div class="main">
