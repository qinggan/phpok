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
class lang_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_list()
	{
		$langlist = array("en"=>"English","cn"=>"简体中文","big5"=>"繁体中文");
		if(is_file($this->dir_root."data/xml/langs.xml"))
		{
			$langlist = xml_to_array(file_get_contents($this->dir_root."data/xml/langs.xml"));
		}
		//DIR LIST语言包列表
		$list = $GLOBALS['app']->lib('file')->ls($this->dir_root."data/xml/langs/");
		if(!$list) return false;
		$rslist = "";
		foreach($list AS $key=>$value)
		{
			$value = basename($value);
			$value = substr($value,0,-4);
			if($langlist[$value])
			{
				$rslist[$value] = $langlist[$value];
			}
			else
			{
				$rslist[$value] = strtoupper($value);
			}
		}
		return $rslist;
	}
}
?>