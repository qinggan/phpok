<?php
/***********************************************************
	备注：POMO语言包解析
	版本：5.0.0
	官网：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年04月02日
***********************************************************/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class pomo_lib extends _init_lib
{
	public function __construct()
	{
		parent::__construct();
		include_once($this->dir_extension().'pomo/mo.php');
	}

	public function lang($mofile='')
	{
		if(!$mofile){
			return false;
		}
		$lang = new NOOP_Translations;
		$mo = new MO();
		if (!$mo->import_from_file($mofile)){
			return false;
		}
		$mo->merge_with($lang);
		$lang = &$mo;
		return $lang;
	}
}
?>