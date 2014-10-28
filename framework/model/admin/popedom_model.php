<?php
/***********************************************************
	Filename: {phpok}/model/popedom.php
	Note	: 权限信息参数设置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function delete($id)
	{
		if(!$id) return false;
		//取得项目信息
		$sql = "SELECT p.identifier,p.pid,s.appfile FROM ".$this->db->prefix."popedom p ";
		$sql.= " JOIN ".$this->db->prefix."sysmenu s ON(p.gid=s.id) ";
		$sql.= " WHERE p.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs["appfile"] == "list" && !$rs["pid"])
		{
			$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE pid != '0' && identifier='".$rs["identifier"]."'";
			$rslist = $this->db->get_all($sql,"id");
			if($rslist)
			{
				$idstring = implode(",",array_keys($rslist));
				//删除配置
				$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE id IN(".$idstring.")";
				$this->db->query($sql);
				//删除管理员权限
				$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE pid IN(".$idstring.")";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE id='".$id."'";
		$this->db->query($sql);
		//删除已分配的权限
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE pid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function get_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_condition($condition="")
	{
		if(!$condition) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE ".$condition;
		return $this->db->get_one($sql);
	}

	//取得模块模型下的权限ID
	function get_list($gid,$pid=0)
	{
		if(!$gid) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE gid='".$gid."' AND pid='".$pid."'";
		$sql.= ' ORDER BY taxis ASC,id DESC';
		return $this->db->get_all($sql);
	}

	//更新权限，仅限当前一个
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if(!$id)
		{
			return $this->db->insert_array($data,"popedom");
		}
		else
		{
			return $this->db->update_array($data,"popedom",array("id"=>$id));
		}
	}

	//更新内容模块的权限字段
	function update_popedom_list($data,$gid,$identifier="")
	{
		if(!$identifier || !$gid || !$data || count($data) < 1 || !is_array($data))
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."popedom SET ";
		$str = array();
		foreach($data AS $key=>$value)
		{
			$str[] = $key."='".$value."'";
		}
		$sql .= implode(",",$str);
		$sql .= " WHERE gid='".$gid."' AND identifier='".$identifier."'";
		$this->db->query($sql);
	}

	function is_exists($identifier,$gid)
	{
		if(!$identifier || !$gid) return true;
		$sql = "SELECT id FROM ".$this->db->prefix."popedom WHERE gid='".$gid."' AND identifier='".$identifier."'";
		return $this->db->get_one($sql);
	}

	function get_pid($condition="")
	{
		if(!$condition) return false;
		$sql = "SELECT p.id FROM ".$this->db->prefix."popedom p ";
		$sql.= " JOIN ".$this->db->prefix."sysmenu s ON(p.gid=s.id) ";
		$sql.= " WHERE ".$condition." LIMIT 1";
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return $rs["id"];
		}
		return false;
	}

	function get_all($condition="",$format=true,$ifpid=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."popedom ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		if(!$format) return $rslist;
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			if($ifpid)
			{
				$list[$value["pid"]][$value["id"]] = $value;
			}
			else
			{
				$list[$value['gid']][$value['id']] = $value;
			}
		}
		return $list;
	}
}
?>