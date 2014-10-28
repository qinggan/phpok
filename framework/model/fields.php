<?php
/***********************************************************
	Filename: phpok/model/fields.php
	Note	: 读取 qinggan_fields/qinggan_fields_ext 表操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-01 20:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_all($condition="",$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY identifier ASC,id ASC";
		return $this->db->get_all($sql,$pri_id);
	}

	# 取得指定页面下的字段
	function fields_list($words="",$offset=0,$psize=40,$type="")
	{
		if(!$words) $words = "id,identifier";
		$sql = "SELECT * FROM ".$this->db->prefix."fields ";
		$list = explode(",",$words);
		$list = array_unique($list);
		$words = implode("','",$list);
		$sql .= " WHERE identifier NOT IN ('".$words."') ";
		if($type)
		{
			$sql .= " AND area LIKE '%".$type."%'";
		}
		$sql .= " ORDER BY taxis ASC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function fields_count($words,$type="")
	{
		if(!$words) $words = "id,identifier";
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fields ";
		$list = explode(",",$words);
		$list = array_unique($list);
		$words = implode("','",$list);
		$sql .= " WHERE identifier NOT IN ('".$words."') ";
		if($type)
		{
			$sql .= " AND area LIKE '%".$type."%'";
		}
		return $this->db->count($sql);
	}

	function get_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id IN(".$id.") ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//判断字段是否被使用了
	function is_has_sign($identifier,$id=0)
	{
		if(!$identifier) return true;
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' ";
		if($id)
		{
			$sql .= " AND id !='".$id."' ";
		}
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return true;
		}
		# 检查核心表的字段ID
		$idlist = array("title","phpok","identifier");
		$idlist = $this->_rslist("list",$idlist);
		if($idlist)
		{
			$idlist = array_unique($idlist);
			if(in_array($identifier,$idlist))
			{
				return true;
			}
		}
		return false;
	}

	function tbl_fields($tbl)
	{
		return $this->_rslist($tbl);
	}

	function _rslist($tbl,$idlist=array())
	{
		$sql = "SHOW FIELDS FROM ".$this->db->prefix.$tbl;
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			$idlist = array();
			foreach($rslist AS $key=>$value)
			{
				$idlist[] = $value["Field"];
			}
			return $idlist;
		}
		else
		{
			return false;
		}
	}

	//存储表单
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"fields",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"fields");
		}
	}

	//更新排序
	function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."fields SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//删除字段
	function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得数据表字段设置的字段类型
	function type_all()
	{
		$array = array(
			"varchar"=>"字符串",
			"int"=>"整型",
			"float"=>"浮点型",
			"date"=>"日期",
			"datetime"=>"日期时间",
			"longtext"=>"长文本",
			"longblob"=>"二进制信息"
		);
		return $array;
	}
}