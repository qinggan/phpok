<?php
/***********************************************************
	Filename: {phpok}/form/textarea_www.php
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
		global $app;
		$this->app = $app;
	}

	function format($rs)
	{
		$width = intval($rs['width']) ? intval($rs['width']) : '300';
		$height = intval($rs['height']) ? intval($rs['height']) : '100';
		$html  = '<table style="border:0;margin:0;padding:0" cellpadding="0" cellspacing="0"><tr><td>';
		$html .= '<textarea name="'.$rs["identifier"].'" id="'.$rs["identifier"].'" phpok_id="textarea" ';
		$html .= 'style="'.$rs["form_style"].';width:'.$width.'px;height:'.$height.'px"';
		$html .= '>'.$rs["content"].'</textarea>';
		$html .= "</td></tr></table>";
		return $html;
	}

}