<?php
/*****************************************************************************************
	文件： {phpok}/model/options.php
	备注： 产品属性
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月07日 13时32分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class options_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."attr WHERE site_id='".$this->site_id."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."attr WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function value_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."attr_values WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function values_total($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."attr_values ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function values_list($condition='',$offset=0,$psize=30,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."attr_values ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		$sql.= " LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql,$pri);
	}
}

?>