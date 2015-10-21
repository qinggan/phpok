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

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//取得订单列表
	function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order ";
		if($condition)
		{
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
	function save_product($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"order_product",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"order_product");
		}
	}

	function save_address($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"order_address",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"order_address");
		}
	}

	//通过订单号取得单个订单信息
	function get_one_from_sn($sn,$user='')
	{
		if(!$sn) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE sn='".$sn."'";
		if($user)
		{
			$sql .= " AND user_id='".$user."'";
		}
		return $this->db->get_one($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE id='".$id."'";
		return $this->db->get_one($sql);
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

	function product_delete($id)
	{
		if(!$id) return false;
		$rs = $this->product_one($id);
		if(!$rs) return false;
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


	public function log_save($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['who'] && $_SESSION['user_id']){
			$user = $this->model('user')->get_one($_SESSION['user_id']);
			$data['who'] = $user['user'];
		}
		if(!$data['addtime']){
			$data['addtime'] = $this->time;
		}
		return $this->db->insert_array($data,'order_log');
	}

	public function log_list($order_id)
	{
		$sql = "SELECT id,addtime,who,note FROM ".$this->db->prefix."order_log WHERE order_id='".$order_id."' ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}
}

?>