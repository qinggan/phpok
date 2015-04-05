<?php
/***********************************************************
	Filename: {phpok}/api/logout_control.php
	Note	: 会员退出接口
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		unset($_SESSION['user_id'],$_SESSION['user_gid'],$_SESSION['user_name']);
		$this->json(true);
	}
}
?>