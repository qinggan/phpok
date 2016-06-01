<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/workflow_model.php
	备注： 工作流后台管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年06月20日 14时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class workflow_model extends workflow_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_one($id)
	{
		$sql = " SELECT w.*,a.account,l.title FROM ".$this->db->prefix."workflow w ";
		$sql.= " JOIN ".$this->db->prefix."adm a ON(w.admin_id=a.id) ";
		$sql.= " JOIN ".$this->db->prefix."list l ON(w.tid=l.id) ";
		$sql.= " WHERE w.id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_tid($id)
	{
		$sql = " SELECT w.*,a.account,l.title FROM ".$this->db->prefix."workflow w ";
		$sql.= " JOIN ".$this->db->prefix."adm a ON(w.admin_id=a.id) ";
		$sql.= " JOIN ".$this->db->prefix."list l ON(w.tid=l.id) ";
		$sql.= " WHERE w.tid='".$id."'";
		return $this->db->get_one($sql);
	}

	public function save($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->insert_array($data,'workflow');
	}

	public function update($data,$id=0)
	{
		if(!$data || !is_array($data) || !$id){
			return false;
		}
		return $this->db->update_array($data,'workflow',array('id'=>$id));
	}

	public function chk($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."workflow";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function get_all($condition='',$offset=0,$psize=30)
	{
		$sql  = "SELECT w.*,l.title,a.account,p.title project_title FROM ".$this->db->prefix."workflow w ";
		$sql .= " JOIN ".$this->db->prefix."adm a ON(w.admin_id=a.id) ";
		$sql .= " JOIN ".$this->db->prefix."list l ON(w.tid=l.id) ";
		$sql .= " JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY w.dateline DESC,w.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function total($condition='')
	{
		$sql  = "SELECT count(w.id) FROM ".$this->db->prefix."workflow w ";
		$sql .= " JOIN ".$this->db->prefix."adm a ON(w.admin_id=a.id) ";
		$sql .= " JOIN ".$this->db->prefix."list l ON(w.tid=l.id) ";
		$sql .= " JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."workflow WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}

?>