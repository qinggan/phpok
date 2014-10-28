<?php
/***********************************************************
	Filename: {phpok}/api/vcode_control.php
	Note	: 验证码
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月1日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$info = $this->lib("vcode")->word();
		$appid = $this->get("id");
		if(!$appid) $appid = $this->app_id;
		$_SESSION["vcode_".$appid] = md5(strtolower($info));
		$this->lib("vcode")->create();
	}
}
?>