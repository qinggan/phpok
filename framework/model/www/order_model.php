<?php
/***********************************************************
	Filename: {phpok}/model/www/order_model.php
	Note	: 订单信息及管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model extends order_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//取得订单列表
	function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$status_list = $this->status_list();
		$order_idlist = array();
		foreach($rslist as $key=>$value){
			$value['status_info'] = ($status_list && $status_list[$value['status']]) ? $status_list[$value['status']] : $value['status'];
			$rslist[$key] = $value;
			$order_idlist[] = $value['id'];
		}
		$order_ids = implode(",",$order_idlist);
		$sql = "SELECT SUM(qty) as qty,order_id FROM ".$this->db->prefix."order_product WHERE order_id IN(".$order_ids.") GROUP BY order_id";
		$tmplist = $this->db->get_all($sql,'order_id');
		if($tmplist){
			foreach($rslist as $key=>$value){
				$value['qty'] = $tmplist[$value['id']] ? $tmplist[$value['id']]['qty'] : 0;
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."order ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	//取得订单的最大ID号，再此基础上+1
	function maxid()
	{
		$sql = "SELECT MAX(id) id FROM ".$this->db->prefix."order";
		$rs = $this->db->get_one($sql);
		if(!$rs) return '1';
		return ($rs['id']+1);
	}

	function address_shipping($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."' AND type_id='shipping' ORDER BY type_id ASC";
		return $this->db->get_one($sql);
	}

	function address_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."' ORDER BY type_id ASC";
		return $this->db->get_all($sql,'type_id');
	}

	//删除订单操作
	function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order WHERE id=".intval($id);
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_address WHERE order_id=".intval($id);
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id=".intval($id);
		$this->db->query($sql);
		return true;
	}

	public function product_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->product_one($id);
		if(!$rs){
			return false;
		}
		$oid = $rs['order_id'];
		//删除产品
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		$this->db->query($sql);
		//更新订单金额
		$sql = "SELECT SUM(price) FROM ".$this->db->prefix."order_product WHERE order_id='".$oid."'";
		$total = $this->db->count($sql);
		$sql = "UPDATE ".$this->db->prefix."order SET price='".$total."',pay_price='".$total."' WHERE id='".$oid."'";
		$this->db->query($sql);
	}

	function product_one($id)
	{
		if(!$id) return false;
		return $this->db->get_one("SELECT * FROM ".$this->db->prefix."order_product WHERE id='".$id."'");
	}
}