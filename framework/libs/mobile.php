<?php
/***********************************************************
	备注：手机版验证，通过Extension里的第三方接入来验证
	版本：5.0.0
	官网：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年04月02日
***********************************************************/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class mobile_lib extends _init_lib
{
	private $obj;
	public function __construct()
	{
		parent::__construct();
		$file = $this->dir_extension().'mobile/Mobile_Detect.php';
		if(file_exists($file)){
			include_once($file);
		}
		$this->obj = new Mobile_Detect();
	}

	public function is_mobile()
	{
		return $this->obj->isMobile();
	}
}
?>