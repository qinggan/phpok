<?php
/***********************************************************
	Filename: {phpok}/model/type.php
	Note	: 类型
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-11 10:01
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class type_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."type WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_id($sign)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."type WHERE sign='".$sign."'";
		return $this->db->get_one($sql);
	}
}
?>