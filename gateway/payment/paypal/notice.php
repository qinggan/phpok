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
		$price = $app->get('amt');
		$sn = $app->get('item_number');
		$checkcode = $app->get('cm');
		$tx = $app->get('tx');
		if(!$checkcode || !$price || !$sn){
			return false;
		}
		$chk = $paypal->check($price,$sn,$checkcode);
		if(!$chk){
			return false;
		}
		$payment_status = $app->get('st');
		if($payment_status != 'Completed'){
			return false;
		}
		$info = $_GET;
		unset($info['c'],$info['f'],$info['id']);
		if($info['_noCache']){
			unset($info['_noCache']);
		}
		$p_array = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$p_array = array_merge($p_array,$info);
		$array = array('status'=>1,'ext'=>serialize($p_array));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($p_array));
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge' && $p_array['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}