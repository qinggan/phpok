<?php
/***********************************************************
	Filename: {phpok}/form/url_admin.php
	Note	: 网址关联配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-15 11:46
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_form
{
	function __construct()
	{
		//
	}

	function config()
	{
		$file = $GLOBALS['app']->dir_phpok."form/html/url_admin.html";
		$GLOBALS['app']->view($file,'abs-file');
	}
	
	function format($rs)
	{
		if($rs["content"] && is_string($rs["content"]))
		{
			$tmp = unserialize($rs["content"]);
			if($tmp && is_array($tmp) && count($tmp)>0)
			{
				$rs["content"] = $tmp;
			}
			else
			{
				$tmp["default"] = $rs["content"];
				$tmp["rewrite"] = "";
				$rs["content"] = $tmp;
			}
		}
		if(!$rs['content'])
		{
			$rs['content'] = array('default'=>'','rewrite'=>'');
		}
		$GLOBALS['app']->assign("_rs",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/url_form_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("_rs");
		return $content;
	}
}
?>