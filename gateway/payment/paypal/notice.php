<?php
/*****************************************************************************************
	文件： payment/paypal/notice.php
	备注： 支付通知页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月24日 12时45分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class paypal_notice
{
	public $paydir;
	public $order;
	public $payment;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/paypal/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."lib/paypal.php");
	}

	function submit()
	{
		global $app;
		if($this->order['status']){
			return true;
		}
		$paypal = new paypal_payment($this->param['param']["payid"],$this->param['param']["at"]);
		$paypal->set_value("action_url",$this->param['param']["action"]);
		$price = $app->get('mc_gross');
		$sn = $app->get('invoice');
		$checkcode = $app->get('custom');
		if(!$checkcode || !$price || !$sn){
			return false;
		}
		$chk = $paypal->check($price,$sn,$checkcode);
		if(!$chk){
			return false;
		}
		$payment_status = $app->get('payment_status');
		if($payment_status != 'Completed'){
			return false;
		}
		$p_array = array();
		$p_array['txn_id'] = $app->get('txn_id');
		$p_array['txn_type'] = $app->get('txn_type');
		$p_array['mc_fee'] = $app->get('mc_fee');
		$p_array['mc_currency'] = $app->get('mc_currency');
		$p_array['mc_gross'] = $price;
		$p_array['payer_email'] = $app->get('payer_email');
		$p_array['first_name'] = $app->get('first_name');
		$p_array['last_name'] = $app->get('last_name');
		$p_array['payer_business_name'] = $app->get('payer_business_name');
		$p_array['payer_status'] = $app->get('payer_status');
		$p_array['exchange_rate'] = $app->get('exchange_rate');
		$p_array['payment_date'] = $app->get('payment_date');
		$array = array('status'=>1,'ext'=>serialize($p_array));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$app->model('order')->update_order_status($order['id'],'paid');
				$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
				$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
				$app->model('order')->log_save($log);
				//增加order_payment
				$array = array('order_id'=>$order['id'],'payment_id'=>$this->param['id']);
				$array['title'] = $this->param['title'];
				$array['price'] = $price;
				$array['dateline'] = $app->time;
				$array['ext'] = serialize($p_array);
				$order_payment = $app->model('order')->order_payment($order['id']);
				if(!$order_payment){
					$app->model('order')->save_payment($array);
				}else{
					$app->model('order')->save_payment($array,$order_payment['id']);
				}
			}
		}
		return true;
	}
}
?>