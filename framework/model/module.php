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
class module_model_base extends phpok_model
{
	private $_cache;
	public function __construct()
	{
		parent::model();
	}

	public function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module ";
		if($status){
			$sql .= " WHERE status='".$status."' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//取得扩展字段的所有扩展信息
	public function fields_all($module_id=0,$pri_id="")
	{
		if(!$module_id){
			return false;
		}
		$cache_id = 'fields_all_'.$module_id.'_'.$pri_id;
		if(isset($this->_cache[$cache_id])){
			return $this->_cache[$cache_id];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='".$module_id."' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,$pri_id);
		if($rslist){
			$this->_cache[$cache_id] = $rslist;
			return $rslist;
		}
		return false;
	}

	public function f_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//检查表是否存在
	public function chk_tbl_exists($id,$mtype=0)
	{
		if(!$id){
			return false;
		}
		$mlist = $this->db->list_tables();
		if(!$mlist){
			return false;
		}
		$tblname = $mtype ? $this->db->prefix.$id : $this->db->prefix."list_".$id;
		if(in_array($tblname,$mlist)){
			return true;
		}
		return false;
	}

	public function tbl_fields_list($tbl)
	{
		return $this->db->list_fields($tbl);
	}

	/**
	 * 模块字段内容
	 * @参数 $id 字段 ID
	**/
	public function field_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}