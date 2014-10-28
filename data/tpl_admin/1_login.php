<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Expires" content="wed, 26 feb 1997 08:21:57 gmt" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<title>管理员登录</title>
<link href="css/login.css" rel="stylesheet" type="text/css" />
<link href="css/artdialog.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo phpok_url(array('ctrl'=>'js','ext'=>'admin.login'));?>"></script>
<script type="text/javascript">
if (self.location != top.location) top.location = self.location;
$(document).ready(function(){
	login_code("<?php echo $sys['app_id'];?>");
});
</script>
</head>
<body>
<div class="top">
	<?php if($config['adm_logo180']){ ?>
	<div class="logo"><div><img src="<?php echo $config['adm_logo180'];?>" border="0" /></div></div>
	<?php } ?>
</div>
<div class="main">
	<div class="box">
		<form method="post" id="adminlogin" onsubmit="return admlogin()">
		<table width="360" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td height="30">管理员账号 </td>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td height="40"><input name="username" type="text" class="user user_bg1" id="username" tabindex="1" /></td>
			<td colspan="2">&nbsp;</td>
		</tr>
		<?php if($vcode){ ?>
			<tr>
				<td width="209" height="30">管理员密码</td>
				<td colspan="2">验证码</td>
			</tr>
			<tr>
				<td height="40"><input name="password" id="password" type="password" class="user user_bg2" tabindex="2" /></td>
				<td width="72"><input name="code_id" type="text" class="user user_bg3" id="code_id" tabindex="3" style="ime-mode:disabled" /></td>
				<td width="79"><img src="images/blank.gif" border="0" align="absmiddle" style="cursor:pointer;" onclick="login_code('<?php echo $sys['app_id'];?>')" id="src_code"></td>
			</tr>
		<?php } else { ?>
			<tr>
				<td width="209" height="30" colspan="3">管理员密码</td>
			</tr>
			<tr>
				<td height="40"><input name="password" id="password" type="password" class="user user_bg2" /></td>
			</tr>
		<?php } ?>
		<tr>
			<td height="50" colspan="3" valign="bottom"><input type="image" src="images/but.png" /></td>
		</tr>
		<tr>
			<td height="30" colspan="3">推荐使用：Chrome / Firefox / IE8-11 访问本系统</td>
		</tr>
		
		</table>
		</form>
	</div>
	<div class="bottom"></div>
</div>
</body>
</html>
