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
class payment_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_all($site_id=0,$status=0)
	{
		$site_id = $site_id ? $site_id.",0" : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE site_id IN(".$site_id.") ";
		if($status)
		{
			$sql.= " AND status=1 ";
		}
		$sql .= ' ORDER BY taxis ASC, id DESC ';
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".$id;
		return $this->db->get_one($sql);
	}

	function get_code($code)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->get_one($sql);
	}

	//存储支付配置
	function save($data)
	{
		if(!$data || !is_array($data)) return false;
		if(!$data['code']) return false;
		$rs = $this->get_code($data['code']);
		if($rs)
		{
			return $this->db->update_array($data,'payment',array('id'=>$rs['id']));
		}
		else
		{
			return $this->db->insert_array($data,'payment');
		}
	}

	//更新状态
	function status($id=0,$status=0,$is_id=false)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET status='".$status."' WHERE ";
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
}
?>