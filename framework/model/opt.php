<?php
/***********************************************************
	Filename: model/opt.php
	Note	: 可选组
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-02 19:41
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_model_base extends phpok_model
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

	# 取得全部的选项组
	function group_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	# 取得某个组信息
	function group_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 取得值列表
	function opt_list($condition="",$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		$sql .= " LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function opt_all($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}

	# 取得数量总数
	function opt_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."opt WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	function opt_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function opt_one_condition($condition)
	{
		if(!$condition) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE ".$condition;
		return $this->db->get_one($sql);
	}

	public function opt_val($gid,$val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$array = array('val'=>$val,'title'=>($rs['title'] ? $rs['title'] : $val));
		return $array;
	}

	# 检测值是否重复
	function chk_val($gid,$val,$pid=0,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$sql.= " AND parent_id='".$pid."'";
		if($id)
		{
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	//取得父子关系的数组
	function opt_parent(&$list,$pid=0)
	{
		if($pid)
		{
			$rs = $this->opt_one($pid);
			$list[] = $rs;
			if($rs["parent_id"])
			{
				$this->opt_parent($list,$rs["parent_id"]);
			}
		}
	}

	//取得子项列表
	function opt_son(&$list,$id=0)
	{
		$condition = "parent_id='".$id."'";
		$tmplist = $this->opt_all($condition);
		if($tmplist)
		{
			foreach($tmplist AS $key=>$value)
			{
				$list[] = $value;
				$this->opt_son($list,$value["id"]);
			}
		}
	}
}
?>