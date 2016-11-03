<?php
/***********************************************************
	Filename: {phpok}/model/payment.php
	Note	: 付款管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月23日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_model_base extends phpok_model
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

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".intval($id);
		return $this->db->get_one($sql);
	}

	function get_code($code)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->get_one($sql);
	}

	//更新状态
	function status($id=0,$status=0,$is_id=false)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET status='".$status."' WHERE ";
		$sql.= $is_id ? " id='".$id."'" : " code='".$id."'";
		return $this->db->query($sql);
	}

	//更新手机端状态
	function wap($id=0,$wap=0,$is_id=false)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET wap='".$wap."' WHERE ";
		$sql.= $is_id ? " id='".$id."'" : " code='".$id."'";
		return $this->db->query($sql);
	}

	//删除支付接口
	function delete_code($code)
	{
		if(!$code) return false;
		$sql = "DELETE FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->query($sql);
	}

	public function log_check($sn)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."'";
		return $this->db->get_one($sql);
	}

	public function log_update($data,$id=0)
	{
		if(!$id || !$data || !is_array($data)){
			return false;
		}
		return $this->db->update_array($data,'payment_log',array('id'=>$id));
	}

	public function log_create($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->insert_array($data,'payment_log');
	}

	public function log_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function log_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment_log WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}