<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/order_model.php
	备注： 订单相关数据库操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月08日 11时36分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model extends order_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//后台订单删除操作
	public function delete($id)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		//删除订单主表
		$sql = "DELETE FROM ".$this->db->prefix."order WHERE id=".$id;
		$this->db->query($sql);
		//删除订单地址信息
		$sql = "DELETE FROM ".$this->db->prefix."order_address WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单物流信息
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单发票信息
		$sql = "DELETE FROM ".$this->db->prefix."order_invoice WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单日志
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_id=".$id;
		$this->db->query($sql);
		//删除付款信息
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单产品信息
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id=".$id;
		$this->db->query($sql);
		return true;
	}

	//保存订单各种状态下的价格
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}

	public function delete_order_price($order_id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		return $this->db->query($sql);
	}

	public function log_save($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['who']){
			$adminer = $this->model('admin')->get_one($_SESSION['admin_id']);
			$who = $adminer['fullname'] ? $adminer['fullname'].'('.$adminer['account'].')' : $adminer['account'];
			$data['who'] = $who;
		}
		$data['addtime'] = $this->time;
		return $this->db->insert_array($data,'order_log');
	}

	public function log_list($order_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_log WHERE order_id='".$order_id."' ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}

	public function get_list($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT o.*,p.title pay_title,p.price pay_price,p.dateline pay_dateline,u.user ";
		$sql.= "FROM ".$this->db->prefix."order o ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order_payment p ON(o.id=p.order_id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY o.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order_payment p ON(o.id=p.order_id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function express_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE order_id='".$id."' AND express_id!=0 ";
		$sql.= "ORDER BY addtime ASC";
		return $this->db->get_all($sql);
	}

	public function express_save($data)
	{
		return $this->db->insert_array($data,'order_express');
	}

	public function express_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function express_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_express_id='".$id."'";
		$this->db->query($sql);
		return true;
	}
	
}

?>