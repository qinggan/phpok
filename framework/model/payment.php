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

	public function log_check($sn,$type='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."'";
		if($type){
			$sql .= " AND type='".$type."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 检查订单是否有未支付日志
	 * @参数 $sn 订单标识
	 * @参数 $type 类型
	**/
	public function log_check_notstatus($sn,$type='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."' AND status=0";
		if($type){
			$sql .= " AND type='".$type."'";
		}
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
		if(is_numeric($data['currency_id'])){
			if(!$data['currency_rate'] || $data['currency_rate'] < 0.00000001){
				$currency = $this->model('currency')->get_one($data['currency_id']);
				$data['currency_rate'] = $currency['val'];
			}
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

	/**
	 * 删除未支付完成的支付请求
	 * @参数 $sn 订单编号
	 * @参数 $type 订单类型
	**/
	public function log_delete_notstatus($sn,$type='')
	{
		if(!$sn){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."' AND status=0";
		if($type){
			$sql .= " AND type='".$type."'";
		}
		return $this->db->query($sql);
	}
}