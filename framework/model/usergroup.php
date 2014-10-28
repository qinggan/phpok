<?php
/***********************************************************
	Filename: {phpok}/model/usergroup.php
	Note	: 会员组模块
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class usergroup_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_all($condition="",$pri="")
	{
		$sql = " SELECT * FROM ".$this->db->prefix."user_group ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}

	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"user_group",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"user_group");
			return $insert_id;
		}
	}

	function get_default($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_default='1'";
		if($status)
		{
			$sql .= " AND status=1"; 
		}
		return $this->db->get_one($sql);
	}

	//删除操作
	function del($id)
	{
		$default_rs = $this->get_default();
		if(!$default_rs) return false;
		//删除会员所属组字段
		$sql = "UPDATE ".$this->db->prefix."user SET group_id='".$default_rs["id"]."' WHERE group_id='".$id."'";
		$this->db->query($sql);
		//删除主表字段
		$sql = "DELETE FROM ".$this->db->prefix."user_group WHERE id='".$id."'";
		$this->db->query($sql);
		//删除扩展字段
		ext_delete("usergroup-".$id);
	}

	function set_default($id=0)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."user_group SET is_default='0' WHERE is_default='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_group SET is_default='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function set_guest($id=0)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."user_group SET is_guest='0' WHERE is_guest='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_group SET is_guest='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function get_guest($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_guest=1 ";
		if($status)
		{
			$sql .= " AND status=1 ";
		}
		return $this->db->get_one($sql);
	}

	function set_status($id,$status=0)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."user_group SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
		phpok_delete_cache("group");
		return true;
	}
}
?>