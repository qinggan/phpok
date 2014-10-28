<?php
/***********************************************************
	Filename: {phpok}/form/password_admin.php
	Note	: 文本框
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class password_form
{
	function __construct()
	{
	}

	function config()
	{
		$html = $GLOBALS['app']->dir_phpok."form/html/password_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	function format($rs)
	{
		if($rs["content"] && $rs["password_type"] == "show" && strlen($rs["content"]) > 2)
		{
			$length = strlen($rs["content"]);
			$new_str = "";
			for($i=0;$i<($length-2);$i++)
			{
				$new_str .= "*";
			}
			$old = substr($rs["content"],1,($length-2));
			$rs["content"] = str_replace($old,$new_str,$rs["content"]);
		}
		if($rs["content"] && $rs["password_type"] == "md5" && strlen($rs["content"]) != 32)
		{
			$rs["content"] = "";
		}
		$GLOBALS['app']->assign("rs",$rs);
		$GLOBALS['app']->assign("current_date",date("Y-m-d",$GLOBALS['app']->time));
		$file = $GLOBALS['app']->dir_phpok."form/html/password_format_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("rs");
		$GLOBALS['app']->unassign("current_date");
		return $content;
	}
}