<?php
/***********************************************************
	Filename: {phpok}/admin/logout_control.php
	Note	: 退出操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年04月25日 10时28分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$admin_name = $_SESSION["admin_account"];
		session_destroy();
		error("管理员 <span class='red'>".$admin_name."</span> 成功退出",$this->url("login"),"ok");
	}
}
?>