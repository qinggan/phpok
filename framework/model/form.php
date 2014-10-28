<?php
/***********************************************************
	Filename: {phpok}/model/form.php
	Note	: 表单选择器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:34
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_model extends phpok_model
{
	public $info = "";
	function __construct()
	{
		parent::model();
		$this->info = xml_to_array(file_get_contents($this->dir_phpok.'system.xml'));
	}

	function form_all()
	{
		if($this->info['form'])
		{
			return $this->info['form'];
		}
		return false;
	}

	function format_all()
	{
		if($this->info['format'])
		{
			return $this->info['format'];
		}
		return false;
	}

	//字段类型
	function field_all()
	{
		if($this->info['field'])
		{
			return $this->info['field'];
		}
		return false;
	}
}
?>