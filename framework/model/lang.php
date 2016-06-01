<?php
/***********************************************************
	Filename: {phpok}/model/lang.php
	Note	: 语言包管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月20日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class lang_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	function get_list()
	{
		$langlist = array("cn"=>"简体中文");
		if(is_file($this->dir_root."data/xml/langs.xml"))
		{
			$langlist = $this->lib('xml')->read($this->dir_root.'data/xml/langs.xml');
		}
		return $langlist;
	}
}
?>