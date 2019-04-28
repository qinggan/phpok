<?php
/**
 * 模型管理维护
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
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
		return $this->db->get_all($sql,$pri);
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
	public function chk_tbl_exists($id,$mtype=0,$tbl='')
	{
		if(!$id){
			return false;
		}
		$mlist = $this->db->list_tables();
		if(!$mlist){
			return false;
		}
		if(!$tbl){
			$tbl = "list";
		}
		$tblname = $mtype ? $this->db->prefix.$id : $this->db->prefix.$tbl."_".$id;
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