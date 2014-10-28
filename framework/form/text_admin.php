<?php
/***********************************************************
	Filename: {phpok}/form/text_admin.php
	Note	: 文本框
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
		global $app;
		$GLOBALS['app'] = $app;
	}

	//扩属属性配置
	function config()
	{
		$html = $GLOBALS['app']->dir_phpok."form/html/text_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	//GET

	function format($rs)
	{
		//针对扩展文本
		if($rs['format'] == 'time')
		{
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $GLOBALS['app']->time;
			$rs['content'] = date($format,$time);
		}
		//当有扩展按钮为颜色选择器时，加载颜色选择器的JS文件
		if($rs['form_btn'] == 'color')
		{
			$GLOBALS['app']->addjs('js/jscolor/jscolor.js');
		}
		//当控件含有时间和日期时加载
		else if($rs['form_btn'] == 'date' || $rs['form_btn'] == 'datetime')
		{
			$GLOBALS['app']->addjs('js/laydate/laydate.js');
		}
		//未设置表单宽度时，使用200作为默认宽度
		if(!$rs['width'] || intval($rs['width'])<1) $rs['width'] = '200';
		//封装并格式化CSS
		$css = $rs['form_style'] ? $rs['form_style'].';width:'.intval($rs['width']).'px;' : 'width:'.intval($rs['width']).'px';
		$rs['form_style'] = $GLOBALS['app']->lib('common')->css_format($css);
		//附加到扩展
		unset($rs['ext']);
		if($rs['ext_quick_words'])
		{
			$tmp = explode("\n",$rs['ext_quick_words']);
			foreach($tmp as $key=>$value)
			{
				if(!$value || !trim($value))
				{
					unset($tmp[$key]);
				}
				else
				{
					$tmp[$key] = trim($value);
				}
			}
			$rs['ext_quick_words'] = $tmp;
		}
		$GLOBALS['app']->assign("Info",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/text_format_admin.html";
		return $GLOBALS['app']->fetch($file,'abs-file');
	}

}