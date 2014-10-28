<?php
/***********************************************************
	Filename: {phpok}/form/user_admin.php
	Note	: 关联会员账号
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-15 11:46
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_form
{
	function __construct()
	{
		//
	}

	function config()
	{
		$file = $GLOBALS['app']->dir_phpok."form/html/user_admin.html";
		$GLOBALS['app']->view($file,'abs-file');
	}
	
	function format($rs)
	{
		$content = $rs['content'];
		if($rs["is_multiple"])
		{
			$content = $content ? implode(",",array_keys($content)) : "";
		}
		else
		{
			$content = $rs['content'];
		}
		$GLOBALS['app']->assign("edit_rs_content",$content);
		$GLOBALS['app']->assign("edit_rs",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/user_form_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("rs");
		$GLOBALS['app']->unassign("rslist");
		return $content;
	}
}
?>