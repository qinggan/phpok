<?php
/***********************************************************
	Filename: {phpok}/www/logout_control.php
	Note	: 会员退出操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年07月01日 05时33分
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
		$nickname = $_SESSION["user_name"];
		unset($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['user_gid']);
		error(P_Lang('会员{user}成功退出',array('user'=>'<span class="red"> '.$nickname.' </span>')),$this->url,'ok');
	}
}
?>