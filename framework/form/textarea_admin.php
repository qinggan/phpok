<?php
/***********************************************************
	Filename: {phpok}/form/textarea/admin.php
	Note	: 文本区编辑框
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class textarea_form
{
	function __construct()
	{
		//
	}

	function config()
	{
		$html = $GLOBALS['app']->dir_phpok."form/html/textarea_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	function format($rs)
	{
		$width = $rs['width']>500 ? $rs['width'].'px' : '905px';
		$html  = '<table style="border:0;margin:0;padding:0" cellpadding="0" cellspacing="0"><tr><td>';
		$html .= '<textarea name="'.$rs["identifier"].'" id="'.$rs["identifier"].'" phpok_id="textarea" ';
		$html .= 'style="'.$rs["form_style"].';width:'.$width.';height:'.$rs["height"].'px"';
		$html .= '>'.$rs["content"].'</textarea>';
		$html .= "</td></tr></table>";
		return $html;
	}

}