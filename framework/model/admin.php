<?php
/***********************************************************
	Filename: phpok/model/admin.php
	Note	: 管理员信息管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-20 14:20
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	# 通过管理员账号取得管理员信息
	function get_one_from_name($username)
	{
		if(!$username) return false;
		return $this->get_one($username,"account");
	}

	function get_one($id,$field="id")
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."adm WHERE ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	//检测账号
	function check_account($account,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."adm WHERE account='".$account."'";
		if($id)
		{
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	function update_password($id,$password)
	{
		if(!$id || !$password) return false;
		$sql = "UPDATE ".$this->db->prefix."adm SET pass='".$password."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得管理员列表
	function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."adm ";
		if($condition)
		{
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY id DESC ";
		if($psize && intval($psize) > 0)
		{
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	function get_total($condition)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."adm ";
		if($condition)
		{
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	function get_popedom_list($id)
	{
		$sql = "SELECT pid FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			$list[] = $value["pid"];
		}
		return $list;
	}

	function delete($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."adm WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"adm",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"adm");
		}
	}

	//清除权限
	function clear_popedom($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//存储权限
	function save_popedom($data,$id)
	{
		if(!$id || !$data) return false;
		if(!is_array($data)) $data = array($data);
		foreach($data AS $key=>$value)
		{
			$tmp = array("id"=>$id,"pid"=>$value);
			$this->db->insert_array($tmp,"adm_popedom","replace");
		}
		return true;
	}

	function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."adm SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//分类权限
	function cate_popedom($id)
	{
		$sql = "SELECT category FROM ".$this->db->prefix."adm WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['category']) return false;
		$list = explode(",",$rs['category']);
		$rslist = array();
		foreach($list as $key=>$value)
		{
			$t = explode(":",$value);
			$rslist[$t[1]][$t[0]] = true;
		}
		return $rslist;
	}
}
?>