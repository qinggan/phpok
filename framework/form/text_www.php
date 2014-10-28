<?php
/***********************************************************
	Filename: {phpok}/form/text_www.php
	Note	: 前端文本框处理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class text_form
{
	function __construct()
	{
		//
	}

	function format($rs)
	{
		if($rs['format'] == 'time')
		{
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $GLOBALS['app']->time;
			$rs['content'] = date($format,$time);
		}
		if(!$rs['width'] || intval($rs['width'])<1) $rs['width'] = '200';
		$css = $rs['form_style'] ? $rs['form_style'].';width:'.intval($rs['width']).'px;' : 'width:'.intval($rs['width']).'px';
		$rs['form_style'] = $GLOBALS['app']->lib('common')->css_format($css);
		$GLOBALS['app']->assign("_rs",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/text_form_www.html";
		return $GLOBALS['app']->fetch($file,'abs-file');
	}

}