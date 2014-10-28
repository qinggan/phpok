<?php
/***********************************************************
	文件： install/update.php
	备注： phpok4.0.315升级到phpok4.0.378程序
	版本： 4.x;
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年2月22日
***********************************************************/
define("INSTALL_DIR",str_replace("\\","/",dirname(__FILE__))."/");
define("ROOT",INSTALL_DIR."../");
header("Content-type: text/html; charset=utf-8");
define("PHPOK_SET",true);
//输出html
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt"> 
	<title>程序更新</title>
	<style type="text/css">
	body{font-family:'微软雅黑','宋体','Arial';font-size:14px;}
	body div{line-height:170%;}
	a{text-decoration:none;color:darkblue;}
	a:hover{color:red;}
	</style>
</head>
<body>
EOT;

//连接数据库
require_once(ROOT.'config.php');
$dbconfig = $config['db'];
$db_name = "db_".$dbconfig['file'];
$cls_sql_file = ROOT."framework/engine/db/".$dbconfig['file'].".php";
if(!is_file($cls_sql_file))
{
	if($type == "ajax") exit("数据库类文件：".$cls_sql_file."不存在！");
	exit("数据库类文件：".$cls_sql_file."不存在！");
}
include($cls_sql_file);
$db = new $db_name($dbconfig);
//判断数据库连接正确与否
if(!$db->conn_status())
{
	if($type == "ajax") exit("数据库连接失败，请检查您的数据库信息本置。");
	exit("数据库连接失败，请检查您的数据库信息本置。");
}
//更新表属性
$extlist = $db->list_fields($db->prefix."sysmenu");
if(!in_array('ext',$extlist))
{
	$sql = "ALTER TABLE `".$db->prefix."sysmenu`  ADD `ext` VARCHAR(255) NOT NULL COMMENT '扩展菜单'  AFTER `identifier`;";
	$db->query($sql);
	echo '更新数据表：'.$db->prefix."sysmenu，增加字段：ext<br />";
}
echo "请将更新包覆盖到网站上，并执行此页面（打开即执行）<br />";
//删除无用的文件（夹）
include(ROOT."framework/libs/file.php");
$file = new file_lib();

//删除根目录下的ajax
if(is_file(ROOT."ajax/www_edit_jl.php"))
{
	$file->rm(ROOT."ajax/www_edit_jl.php");
	echo '删除文件：ajax/www_edit_jl.php，';
	if(is_file(ROOT."ajax/www_edit_jl.php"))
	{
		echo '<span style="color:red;">失败，请手动删除</span>';
	}
	else
	{
		echo '<span style="color:blue">成功</span>';
	}
	echo '<br />';
}
if(is_file(ROOT."ajax/www_update_dateline.php"))
{
	$file->rm(ROOT."ajax/www_update_dateline.php");
	echo '删除文件：ajax/www_update_dateline.php，';
	if(is_file(ROOT."ajax/www_update_dateline.php"))
	{
		echo '<span style="color:red;">失败，请手动删除</span>';
	}
	else
	{
		echo '<span style="color:blue">成功</span>';
	}
	echo '<br />';
}
if(is_dir(ROOT."framework/ajax"))
{
	$file->rm(ROOT."framework/ajax","folder");
	echo '删除目录：framework/ajax，';
	if(is_dir(ROOT."framework/ajax"))
	{
		echo '<span style="color:red;">失败，请手动删除</span>';
	}
	else
	{
		echo '<span style="color:blue">成功</span>';
	}
	echo '<br />';
}
if(is_dir(ROOT."framework/tpl_default"))
{
	$file->rm(ROOT."framework/tpl_default","folder");
	echo '删除目录：framework/tpl_default，';
	if(is_dir(ROOT."framework/tpl_default"))
	{
		echo '<span style="color:red;">失败，请手动删除</span>';
	}
	else
	{
		echo '<span style="color:blue">成功</span>';
	}
	echo '<br />';
}
if(is_dir(ROOT."framework/www/tj_control.php"))
{
	$file->rm(ROOT."framework/www/tj_control.php");
	echo '删除历史遗留文件：framework/www/tj_control.php，';
	if(is_dir(ROOT."framework/www/tj_control.php"))
	{
		echo '<span style="color:red;">失败，请手动删除</span>';
	}
	else
	{
		echo '<span style="color:blue">成功</span>';
	}
	echo '<br />';
}
//
echo "更新完成，请手动删除：install/update.php 文件<br />";
?>