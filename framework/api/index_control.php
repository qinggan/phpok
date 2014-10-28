<?php
/***********************************************************
	Filename: {phpok}/api/index_control.php
	Note	: API接口默认接入
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		echo '111';
	}

	function vcode_f()
	{
		//
	}
}