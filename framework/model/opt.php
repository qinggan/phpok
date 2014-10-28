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
class opt_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	# 兼容PHP4
	function opt_model()
	{
		$this->__construct();
	}

	# 取得全部的选项组
	function group_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	# 存储选项组，title，选项组名称
	# id，指定要更新的ID，如果没有ID，将添加一条记录
	function group_save($title,$id=0)
	{
		if(!$id)
		{
			syscache_delete("opt");
			return $this->db->insert_array(array("title"=>$title),"opt_group");
		}
		else
		{
			syscache_delete("opt");
			return $this->db->update_array(array("title"=>$title),"opt_group",array("id"=>$id));
		}
	}

	# 删除选项组，同时删除选项组下的内容
	function group_del($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE group_id='".$id."'";
		$this->db->query($sql);
		syscache_delete("opt");
		return true;
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

	# 存储写入的值
	function opt_save($data,$id=0)
	{
		syscache_delete("opt");
		if(!$id)
		{
			return $this->db->insert_array($data,"opt");
		}
		else
		{
			return $this->db->update_array($data,"opt",array("id"=>$id));
		}
	}

	# 删除内容
	function opt_del($id)
	{
		if(!$id) return false;
		syscache_delete("opt");
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->query($sql);
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