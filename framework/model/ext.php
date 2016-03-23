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
class ext_model_base extends phpok_model
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

	# 检查字段是否有被使用
	function check_identifier($identifier,$module)
	{
		if(!$identifier || !$module) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE identifier='".$identifier."' AND module='".$module."'";
		return ($this->db->get_one($sql) ? true : false);
	}

	# 读取模块下的字段内容
	# module，模块名称
	# show_content，是否读取内容，默认true
	function ext_all($module,$show_content=true)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ext WHERE module='".$module."' ORDER BY taxis ASC,id DESC";
		if($show_content){
			$sql = "SELECT e.*,c.content content_val FROM ".$this->db->prefix."ext e ";
			$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(e.id=c.id) ";
			$sql.= "WHERE e.module='".$module."' ";
			$sql.= "ORDER BY e.taxis asc,id DESC";
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if($show_content){
			foreach($rslist AS $key=>$value){
				if($value['content_val']){
					$value["content"] = $value['content_val'];
				}
				unset($value['content_val']);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
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


	//取得所有扩展选项信息
	function get_all($id,$mult = false)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.module FROM ".$this->db->prefix."ext ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		if($mult){
			if(is_array($id)){
				$id = implode(",",$id);
			}
			$id = str_replace(",","','",$id);
			$sql .= " WHERE ext.module IN('".$id."')";
		}else{
			$sql .= " WHERE ext.module='".$id."'";
		}
		$sql .= ' ORDER BY ext.taxis ASC,ext.id DESC';
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist AS $key=>$value){
			if($mult){
				$rs[$value["module"]][$value["identifier"]] = $this->lib('form')->show($value);
			}else{
				$rs[$value["identifier"]] = $this->lib('form')->show($value);
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