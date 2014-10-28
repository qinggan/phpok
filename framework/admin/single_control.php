<?php
/***********************************************************
	Filename: admin/single_control.php
	Note	: 单页面管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-31 20:28
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class single_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$this->view("index");
	}
}
?>