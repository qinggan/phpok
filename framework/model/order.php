<?php
/***********************************************************
	Filename: {phpok}/model/order.php
	Note	: 订单信息及管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model_base extends phpok_model
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
		return $this->db->get_all($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
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

	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"order",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"order");
		}
	}

	//存储商品信息
	public function save_product($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"order_product",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_product");
		}
	}

	function save_address($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"order_address",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_address");
		}
	}

	public function save_invoice($data)
	{
		return $this->db->insert_array($data,'order_invoice','replace');
	}

	//通过订单号取得单个订单信息
	function get_one_from_sn($sn,$user='')
	{
		if(!$sn){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE sn='".$sn."'";
		if($user){
			$sql .= " AND user_id='".$user."'";
		}
		return $this->db->get_one($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function address($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	//取得订单下的产品信息
	function product_list($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_product WHERE order_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist AS $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function invoice($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_invoice WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function product_delete($id)
	{
		if(!$id) return false;
		$rs = $this->product_one($id);
		if(!$rs) return false;
		$oid = $rs['order_id'];
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function product_one($id)
	{
		if(!$id){
			return false;
		}
		return $this->db->get_one("SELECT * FROM ".$this->db->prefix."order_product WHERE id='".$id."'");
	}

	public function update_order_status($id,$status='')
	{
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function save_payment($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'order_payment',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'order_payment');
		}
	}

	public function order_payment($order_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."'";
		return $this->db->get_one($sql);
	}

	public function order_payment_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function order_price($order_id)
	{
		$sql = "SELECT code,price FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$rs[$value['code']] = $value['price'];
		}
		return $rs;
	}

	public function status_list()
	{
		$list = $this->model('site')->order_status_all(false);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$key] = $value['title'];
		}
		return $rslist;
	}
}

?>