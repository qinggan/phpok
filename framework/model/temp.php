<?php
/***********************************************************
	Filename: {phpok}/model/temp.php
	Note	: 临时存储器（适用于自动数据保存）
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-10 00:04
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class temp_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	# 存储内容
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"temp",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"temp");
		}
	}

	//单条存储记录
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."temp WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 检查存储的数据
	function chk($tbl,$admin_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."temp WHERE tbl='".$tbl."' AND admin_id='".$admin_id."'";
		return $this->db->get_one($sql);
	}

	# 清空临时表
	function clean($tbl,$admin_id)
	{
		if(!$tbl || !$admin_id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."temp WHERE tbl='".$tbl."' AND admin_id='".$admin_id."'";
		return $this->db->query($sql);
	}

}
?>