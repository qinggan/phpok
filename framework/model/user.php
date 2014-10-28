<?php
/***********************************************************
	Filename: {$phpok}/model/user.php
	Note	: 会员模块
	Version : 3.0
	Author  : qinggan
	Update  : 2013年5月4日
***********************************************************/
class user_model extends phpok_model
{
	var $psize = 20;
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		if(!$id) return false;
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE u.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$flist = $this->fields_all();
		if(!$flist) return $rs;
		foreach($flist AS $key=>$value)
		{
			$rs[$value["identifier"]] = $GLOBALS['app']->lib("ext")->content_format($value,$rs[$value["identifier"]]);
		}
		return $rs;
	}

	//读取会员列表数据
	function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql.= " ORDER BY u.id DESC ";
		if($psize)
		{
			$offset = intval($offset);
			$sql .= "LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$idlist = array_keys($rslist);
		//获取会员扩展字段
		$flist = $this->fields_all();
		if(!$flist) return $rslist;
		foreach($rslist AS $key=>$value)
		{
			foreach($flist AS $k=>$v)
			{
				if($value[$v["identifier"]])
				{
					$value[$v["identifier"]] = $GLOBALS['app']->lib('ext')->content_format($v,$value[$v['identifier']]);
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}


	//取得总数量
	function get_count($condition="")
	{
		$sql = "SELECT count(u.id) FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"user",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"user");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//检测账号是否冲突
	function chk_name($name,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE user='".$name."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}


	//取得扩展字段的所有扩展信息
	function fields_all($condition="",$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	//存储模块下的字段表
	function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"user_fields",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"user_fields");
		}
	}

	//创建字段
	function create_fields($rs)
	{
		if(!$rs || !is_array($rs)) return false;
		//判断表是否存在此字段，已创建就跳过
		$idlist = $this->tbl_fields_list($this->db->prefix."user_ext");
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
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext ADD `".$rs["identifier"]."`";
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
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//删除字段
	function field_delete($id)
	{
		if(!$id) return false;
		$rs = $this->field_one($id);
		$field = $rs["identifier"];
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext DROP `".$field."`";
		$this->db->query($sql);
		# 删除记录
		$sql = "DELETE FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function save_ext($data)
	{
		if(!$data || !is_array($data)) return false;
		return $this->db->insert_array($data,"user_ext","replace");
	}

	function update_ext($data,$id)
	{
		if(!$data || !is_array($data) || !$id) return false;
		return $this->db->update_array($data,"user_ext",array("id"=>$id));
	}

	//取得全部会员ID
	function get_all_from_uid($uid,$pri="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$uid.")";
		return $this->db->get_all($sql,$pri);
	}

	function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	function uid_from_email($email,$id="")
	{
		if(!$email) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($id)
		{
			$sql.= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	function uid_from_chkcode($code)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE code='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}
}
?>