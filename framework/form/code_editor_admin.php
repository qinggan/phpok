<?php
/***********************************************************
	Filename: {phpok}/form/code_editor_admin.php
	Note	: 代码编辑器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class code_editor_form
{
	function __construct()
	{
	}

	function cssjs()
	{
		$GLOBALS['app']->addjs('js/codemirror/codemirror.js');
		$GLOBALS['app']->addcss('js/codemirror/codemirror.css');
	}

	function config()
	{
		$html = $GLOBALS['app']->dir_phpok."form/html/code_editor_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	function format($rs)
	{
		$this->cssjs();
		$GLOBALS['app']->assign("rs",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/code_editor_form_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("rs");
		return $content;
	}
}