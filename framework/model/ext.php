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

	# 检查字段是否有被使用
	function check_identifier($identifier,$module)
	{
		if(!$identifier || !$module) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' AND ftype='".$module."'";
		return ($this->db->get_one($sql) ? true : false);
	}

	/**
	 * 读取模块下的字段内容
	 * @参数 $module 模块名称
	 * @参数 $show_content 是否读取内容，默认true
	**/
	public function ext_all($module,$show_content=true)
	{
		$rslist = $this->model('fields')->flist($module);
		if(!$rslist){
			return false;
		}
		if($show_content){
			$ids = array();
			foreach($rslist as $key=>$value){
				$ids[] = $value['id'];
			}
			$sql = "SELECT * FROM ".$this->db->prefix."extc WHERE id IN(".implode(",",$ids).")";
			$tmplist = $this->db->get_all($sql);
			$rs = array();
			if($tmplist){
				foreach($tmplist as $key=>$value){
					$rs[$value['id']] = $value['content'];
				}
			}
			foreach($rslist as $key=>$value){
				if(isset($rs[$value['id']]) && $rs[$value['id']] != ''){
					$value['content'] = $rs[$value['id']];
					$rslist[$key] = $value;
				}
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
	public function get_one($id)
	{
		return $this->model('fields')->one($id);
	}


	//取得所有扩展选项信息
	function get_all($id,$mult = false)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM ".$this->db->prefix."fields ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		if($mult){
			if(is_array($id)){
				$id = implode(",",$id);
			}
			$id = str_replace(",","','",$id);
			$sql .= " WHERE ext.ftype IN('".$id."')";
		}else{
			$sql .= " WHERE ext.ftype='".$id."'";
		}
		$sql .= ' ORDER BY ext.taxis ASC,ext.id DESC';
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			if($mult){
				$rs[$value["ftype"]][$value["identifier"]] = $this->lib('form')->show($value);
			}else{
				$rs[$value["identifier"]] = $this->lib('form')->show($value);
			}
		}
		return $rs;
	}

	public function get_all_like($id)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM ".$this->db->prefix."fields ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.ftype LIKE '".$id."%' ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$list[$value["ftype"]][$value["identifier"]] = content_format($value);
		}
		if(!$list || count($list)<1){
			return false;
		}
		return $list;
	}
}