<?php
/***********************************************************
	Filename: {phpok}/model/ext.php
	Note	: 扩展字段管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-05 16:56
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	# 检查字段是否有被使用
	function check_identifier($identifier,$module)
	{
		if(!$identifier || !$module) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE identifier='".$identifier."' AND module='".$module."'";
		return ($this->db->get_one($sql) ? true : false);
	}

	# 存储扩展字段配置信息
	function ext_save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		phpok_delete_cache("cate,project,all,ext,extc");
		if($id)
		{
			return $this->db->update_array($data,"ext",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ext");
		}
	}

	function extc_save($content,$id)
	{
		if(!$id) return false;
		phpok_delete_cache("cate,project,all,ext,extc");
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	# 存储扩展字段内容存储
	function content_save($content,$id)
	{
		if(!$id || !$content) return false;
		phpok_delete_cache("cate,project,all,ext,extc");
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	# 读取模块下的字段内容
	# module，模块名称
	# show_content，是否读取内容，默认true
	function ext_all($module,$show_content=true)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ext WHERE module='".$module."' ORDER BY taxis ASC";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist) return false;
		if($show_content)
		{
			$id_list = array_keys($rslist);
			$id_string = implode(",",$id_list);
			$sql = "SELECT * FROM ".$this->db->prefix."extc WHERE id IN(".$id_string.") ";
			$content_list = $this->db->get_all($sql,"id");
			foreach($rslist AS $key=>$value)
			{
				if($content_list[$key])
				{
					$value["content"] = $content_list[$key]["content"];
				}
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	# 删除字段内容
	function ext_delete($id,$module)
	{
		phpok_delete_cache("cate,project,all,ext,extc");
		$sql = "DELETE FROM ".$this->db->prefix."ext WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	# 取得数据库下的字段
	# tbl 指定数据表名，多个数据表用英文逗号隔开
	# prefix 表名是否带有前缀，默认不带
	function fields($tbl,$prefix=false)
	{
		if(!$tbl) return false;
		$list = explode(",",$tbl);
		$idlist = array();
		foreach($list AS $key=>$value)
		{
			$table = $prefix ? $value : $this->db->prefix.$value;
			$extlist = $this->db->list_fields($table);
			if($extlist)
			{
				$idlist = array_merge($idlist,$extlist);
			}
		}
		foreach($idlist AS $key=>$value)
		{
			$idlist[$key] = strtolower($value);
		}
		return array_unique($idlist);
	}

	# 取得单个字段的配置
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ext WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储表单
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		phpok_delete_cache("cate,project,all,ext,extc");
		if($id)
		{
			return $this->db->update_array($data,"ext",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ext");
		}
	}

	//删除表单
	function del($module)
	{
		phpok_delete_cache("cate,project,all,ext,extc");
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return true;
		foreach($rslist AS $key=>$value)
		{
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value["id"]."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		return $this->db->query($sql);
	}

	//取得所有扩展选项信息
	function get_all($id,$mult = false)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.module FROM ".$this->db->prefix."ext ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		if($mult)
		{
			$id = str_replace(",","','",$id);
			$sql .= " WHERE ext.module IN('".$id."')";
		}
		else
		{
			$sql .= " WHERE ext.module='".$id."'";
		}
		$sql .= ' ORDER BY ext.taxis ASC,ext.id DESC';
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$rs = "";
		foreach($rslist AS $key=>$value)
		{
			if($mult)
			{
				$rs[$value["module"]][$value["identifier"]] = $GLOBALS['app']->lib('ext')->content_format($value,$value['content']);
			}
			else
			{
				$rs[$value["identifier"]] = $GLOBALS['app']->lib('ext')->content_format($value,$value['content']);
			}
		}
		return $rs;
	}

	function get_all_like($id)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.module FROM ".$this->db->prefix."ext ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.module LIKE '".$id."%' ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$list = false;
		foreach($rslist AS $key=>$value)
		{
			$list[$value["module"]][$value["identifier"]] = content_format($value);
		}
		return $list;
	}
}