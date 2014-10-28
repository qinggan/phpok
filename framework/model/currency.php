<?php
/***********************************************************
	Filename: {phpok}/model/currency.php
	Note	: 货币管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年04月24日 01时14分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class currency_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_list($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}

	function get_one($id,$field_id='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE ".$field_id."='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储信息
	function save($data,$id="")
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"currency",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"currency","replace");
		}
	}

	function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function update_sort($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}


	//删除操作
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."currency WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>