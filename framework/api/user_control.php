<?php
/***********************************************************
	Filename: {phpok}/api/user_control.php
	Note	: 会员信息相关API
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function address_f()
	{
		//判断权限是否有获取资格
		if(!$_SESSION['admin_id']) $this->json('您没有权限执行此项操作');
		//
		$uid = $this->get('uid','int');
		if(!$uid) $this->json('未指定会员ID');
		//
		$type = $this->get('type');
		if(!$type) $this->json('未指定要获取的地址类型');
		//判断类型是否正确
		if($type != 'shipping' && $type != 'billing') $this->json("获取地址类型不正确");
	}
}
?>