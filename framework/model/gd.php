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
class gd_model extends phpok_model
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

	# 删除图片方案
	function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."gd WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	# 保存配置信息
	function save($data,$id=0)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		if($id)
		{
			return $this->db->update_array($data,"gd",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"gd");
		}
	}

	# 检测标识是否被使用了
	function identifier_check($identifier)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE identifier='".$identifier."'";
		return $this->db->get_one($sql);
	}

	# 设置编辑器默认读取
	function update_editor($id)
	{
		$sql = "UPDATE ".$this->db->prefix."gd SET editor='0'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."gd SET editor='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	# 取得支持编辑器的图片方案
	function get_editor_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE editor='1'";
		return $this->db->get_one($sql);
	}

}
?>