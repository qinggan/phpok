<?php
/***********************************************************
	Filename: {phpok}/model/menu.php
	Note	: 导航菜单管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-08 16:59
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class menu_model extends phpok_model
{
	var $site_id = 0;
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}
?>