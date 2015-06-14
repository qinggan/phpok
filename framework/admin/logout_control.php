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
		foreach($_SESSION as $key=>$value){
			if(substr($key,0,5) == 'admin' && $key != 'admin_lang_id'){
				unset($_SESSION[$key]);
			}
		}
		error(P_Lang('管理员{admin_name}成功退出',array('admin_name'=>'<span class="red">'.$admin_name.'</span>')),$this->url('login'),'ok');
	}
}
?>