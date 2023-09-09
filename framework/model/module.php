<?php
/**
 * 模型管理维护
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年3月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model_base extends phpok_model
{
	private $_cache;
	public function __construct()
	{
		parent::model();
	}

	public function get_all($status=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module ";
		if($status){
			$sql .= " WHERE status='".$status."' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		return $rslist;
	}

	public function xml_all($rslist)
	{
		foreach($rslist as $key=>$value){
			$rslist[$key] = $this->xml_one($value);
		}
		return $rslist;
	}

	public function xml_one($rs)
	{
		if(!$rs){
			return false;
		}
		$file = $this->dir_data.'xml/fields_'.$rs['id'].'.xml';
		if(!is_file($file)){
			return $rs;
		}
		$tmp = $this->lib('xml')->read($file);
		if(!$tmp){
			return $rs;
		}
		foreach($tmp as $key=>$value){
			if(!isset($rs[$key])){
				$rs[$key] = $value;
			}
		}
		return $rs;
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs;
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
		$rslist = $this->model('fields')->flist($module_id,$pri_id);
		if(!$rslist){
			return false;
		}
		$this->_cache[$cache_id] = $rslist;
		return $rslist;
	}

	public function f_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rslist = $this->xml_all($rslist);
		return $rslist;
	}

	public function check_table_exists($module)
	{
		if(!$module){
			return false;
		}
		if(is_numeric($module)){
			$module = $this->get_one($module);
			if(!$module){
				return false;
			}
		}
		$mlist = $this->db->list_tables();
		if(!$mlist){
			return false;
		}
		$tblname = tablename($module);
		if(in_array($tblname,$mlist)){
			return true;
		}
		return false;
	}

	//检查表是否存在
	public function chk_tbl_exists($module)
	{
		return $this->check_table_exists($module);
	}

	/**
	 * 获取模块的表名
	 * @参数 $module 模块名称，数组或数字
	 * @参数 $is_prefix 是否包含前缀
	 * @返回 表名称
	**/
	public function tablename($module,$is_prefix=true)
	{
		if(!$module){
			return false;
		}
		if(is_numeric($module)){
			$module = $this->get_one($module);
			if(!$module){
				return false;
			}
		}
		if($module['mtype']){
			$tblname = $module['tbname'] ? $module['tbname'] : $module['id'];
		}else{
			$tblname = $module['tbl'] ? $module['tbl']."_".$module['id'] : 'list_'.$module['id'];
		}
		if($is_prefix){
			$tblname = $this->db()->prefix().$tblname;
		}
		return $tblname;
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
		return $this->model('fields')->one($id);
	}

	/**
	 * 可扩展的集成模块
	**/
	public function tblist()
	{
		$list = array();
		$list['list'] = P_Lang('主题');
		$list['cate'] = P_Lang('分类');
		return $list;
	}
}