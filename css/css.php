<?php
/***********************************************************
	Filename: css/css.php
	Note	: CSS样式集合器，用于合并多个CSS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月17日
***********************************************************/
error_reporting(E_ALL ^ E_NOTICE);
function_exists("ob_gzhandler") ? ob_start("ob_gzhandler") : ob_start();
header("Content-type: text/css; charset=utf-8");
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
$file = $_GET["file"];
if(!$file) $file = "style.css";
$list = explode(",",$file);
foreach($list AS $key=>$value)
{
	$value = basename($value);
	if(is_file(ROOT.$value) && strtolower(substr($value,-3)) == "css")
	{
		echo file_get_contents(ROOT.$value);
		echo "\n";
	}
}
