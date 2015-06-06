<?php
/***********************************************************
	Filename: plugins/phpmyadmin/uninstall.php
	Note	: 卸载配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月31日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class uninstall_phpmyadmin extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	//卸载安装
	function index()
	{
		$rs = $this->plugin_info();
		$sysmenu_id = $rs['param']['sysmenu_id'];
		$this->model('sysmenu')->delete($sysmenu_id);
	}
}
?>