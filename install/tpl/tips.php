<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt"> 
<title>友情提示，您正在安装PHPOK4</title>
<style type="text/css">
body{font-size:12px;font-family:"Microsoft Yahei","宋体","Tahoma","Arial"}
.body{margin:100px auto auto auto;width:550px;border:1px solid #8F8F8F;}
.red{color:red;}
.body .tips{height:70px;}
.body .tips .title{margin-left:70px;font-weight:bold;height:20px;padding-top:15px;}
.body .tips .note{margin-left:70px;height:30px;}
.body .tips .txt{margin-left:70px;padding-top:10px;line-height:50px;font-weight:bold;}
.body .notice{background:url("images/notice.jpg") 20px center no-repeat;}
.body .ok{background:url("images/success.jpg") 20px center no-repeat;}
.body .error{background:url("images/error.jpg") 20px center no-repeat;}
</style>
</head>
<body>
<div class="body">
	<div class="tips <?php echo $type;?>">
		<?php if($url){ ?>
		<div class="title"><?php echo $tips;?></div>
		<div class="note"><a href="<?php echo $url;?>">系统将在 <span class="red"><?php echo $time ? $time : 2;?>秒</span> 后跳转，手动跳转请点这里</a></div>
		<?php }else{ ?>
		<div class="txt"><?php echo $tips;?></div>
		<?php } ?>
	</div>
</div>
<?php if($url) { ?>
<script type="text/javascript">
var url = "<?php echo $url;?>";
var mtime = <?php echo $time ? $time : 2;?> * 1000;
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