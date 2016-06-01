<?php
/***********************************************************
	Filename: {phpok}www/user_control.php
	Note	: 会员趣事
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$uid = $this->get("uid");
		if(!$uid){
			error(P_Lang('未指定会员信息'));
		}
		$user_rs = $this->model('user')->get_one($uid);
		$this->assign("user_rs",$user_rs);
		$this->view("user_info");
	}

}
?>