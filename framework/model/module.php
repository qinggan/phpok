<?php
/***********************************************************
	Filename: {phpok}/model/module.php
	Note	: 模型管理维护
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-29 21:06
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module ";
		if($status)
		{
			$sql .= " WHERE status='".$status."' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//取得扩展字段的所有扩展信息
	function fields_all($module_id=0,$pri_id="")
	{
		if(!$module_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE module_id='".$module_id."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	function f_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields ";
		if($condition)
		{
			$sql .= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//存储模块表
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"module",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"module");
		}
	}

	//存储模块下的字段表
	function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"module_fields",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"module_fields");
		}
	}

	//检查模块表是否存在
	function chk_tbl_exists($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SHOW TABLES LIKE '".$this->db->prefix."list_".$id."'";
		return $this->db->get_one($sql);
	}

	//创建系统表
	function create_tbl($id)
	{
		$rs = $this->get_one($id);
		if(!$rs) return false;
		$sql = "CREATE TABLE ".$this->db->prefix."list_".$id." (";
		$sql.= "`id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '主题ID',";
		$sql.= "`site_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '网站ID',";
		$sql.= "`project_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '项目ID',";
		$sql.= "`cate_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '主分类ID',";
		$sql.= "PRIMARY KEY (  `id` ) ,";
		$sql.= "INDEX (  `site_id` ,  `project_id` ,  `cate_id` )";
		$sql.= ") ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '".$rs["title"]."'";
		return $this->db->query($sql);
	}

	//创建字段
	function create_fields($id,$rs)
	{
		if(!$id || !$rs) return false;
		$chk_tbl = $this->chk_tbl_exists($id);
		if(!$chk_tbl)
		{
			return false;
		}
		//判断表是否存在此字段，已创建就跳过
		$idlist = $this->tbl_fields_list($this->db->prefix."list_".$id);
		if($idlist && in_array($rs["identifier"],$idlist))
		{
			return true;
		}
		//判断字段类型是否符合要求，不符合要求跳过创建
		$tlist = array("varchar","int","float","date","datetime","text","longtext","blob","longblob");
		if(!in_array($rs["field_type"],$tlist))
		{
			return false;
		}
		# 创建表字段，这里不加索引等功能，如果在数据量大时，可咨询PHPOK官方进行优化
		$sql = "ALTER TABLE ".$this->db->prefix."list_".$id." ADD `".$rs["identifier"]."`";
		if($rs["field_type"] == "varchar")
		{
			$sql.= " VARCHAR( 255 ) ";
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}
		elseif($rs["field_type"] == "int")
		{
			$sql.= " INT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}
		elseif($rs["field_type"] == "float")
		{
			$sql.= " FLOAT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}
		elseif($rs["field_type"] == "date")
		{
			$sql.= " DATE NULL ";
		}
		elseif($rs["field_type"] == "datetime")
		{
			$sql.= " DATETIME NULL ";
		}
		elseif($rs["field_type"] == "longtext" || $rs["field_type"] == "text")
		{
			$sql.= " LONGTEXT NOT NULL ";
		}
		elseif($rs["field_type"] == "longblob" || $rs["field_type"] == "blob")
		{
			$sql.= " LONGBLOB NOT NULL ";
		}
		$sql.= " COMMENT  '".$rs["title"]."' ";
		return $this->db->query($sql);
	}

	function tbl_fields_list($tbl)
	{
		$sql = "SELECT FIELDS FROM ".$tbl;
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$idlist[] = $value["Field"];
			}
		}
		return $idlist;
	}

	function field_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//删除字段
	function field_delete($id)
	{
		if(!$id) return false;
		$rs = $this->field_one($id);
		$mid = $rs["module_id"];
		$field = $rs["identifier"];
		$sql = "ALTER TABLE ".$this->db->prefix."list_".$mid." DROP `".$field."`";
		$this->db->query($sql);
		# 删除记录
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//删除模块操作
	function delete($id)
	{
		if(!$id) return false;
		$this->db->query($sql);
		//删除表
		if($this->chk_tbl_exists($id))
		{
			$sql = "DROP TABLE ".$this->db->prefix."list_".$id;
			$this->db->query($sql);
		}
		//删除不用的标识
		$sql = "DELETE FROM ".$this->db->prefix."id WHERE type_id='content' AND id IN(SELECT id FROM ".$this->db->prefix."list WHERE module_id=".$id.")";
		$this->db->query($sql);
		//删除不用的主题
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
		$this->db->query($sql);
		//更新项目信息
		$sql = "UPDATE ".$this->db->prefix."project SET module='0' WHERE module='".$id."'";
		$this->db->query($sql);
		//删除扩展字段
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE module_id='".$id."'";
		$this->db->query($sql);
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//更新排序
	function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

}
?>