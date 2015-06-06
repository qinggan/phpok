<?php
/***********************************************************
	Filename: plugins/phpmyadmin/setting.php
	Note	: phpmyadmin配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月31日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_phpmyadmin extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function index()
	{
		return $this->plugin_tpl('install.html');
	}

	function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['phpmyadminurl'] = $this->get('phpmyadminurl');
		if(!$ext['phpmyadminurl']){
			error('未指定PhpMyAdmin地址',$this->url('plugin','install','id='.$id),'error');
		}
		$this->plugin_save($ext,$id);
	}
}
?>