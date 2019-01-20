<?php
/***********************************************************
	Filename: {phpok}/model/gd.php
	Note	: 图片方案存储器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-27 15:25
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gd_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_all($id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd ORDER BY id DESC";
		return $this->db->get_all($sql,$id);
	}

	function get_one($id,$field="id")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_editor_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE editor='1'";
		return $this->db->get_one($sql);
	}
}