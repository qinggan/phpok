<?php
/*****************************************************************************************
	文件： payment/paypal/notify.php
	备注： Notify通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月25日 10时41分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class paypal_notify
{
	public $paydir;
	public $order;
	public $payment;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'payment/paypal/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."lib/paypal.php");
	}


	public function submit()
	{
		$paypal = new paypal_payment($this->param['param']["payid"],$this->param['param']["at"]);
		$paypal->set_value("action_url",$this->param['param']["action"]);
		$price = $GLOBALS['app']->get('mc_gross');
		$sn = $GLOBALS['app']->get('invoice');
		$checkcode = $GLOBALS['app']->get('custom');
		if(!$checkcode){
			phpok_log(P_Lang('异步传输：没有自定义验证串'));
			exit('error');
		}
		if(!$price || !$sn){
			phpok_log(P_Lang('数据异步'));
			exit('error');
		}
		$chk = $paypal->check($price,$sn,$checkcode);
		if(!$chk){
			phpok_log(P_Lang('异步传输：验证不能过'));
			exit('error');
		}
		$payment_status = $GLOBALS['app']->get('payment_status');
		if($payment_status != 'Completed'){
			phpok_log(P_Lang('异步传输：支付状态是'.$payment_status));
			exit('error');
		}
		$pay_date = $GLOBALS['app']->get('payment_date');
		if($pay_date){
			$pay_date = strtotime($pay_date);
			if(!$pay_date){
				$pay_date = $GLOBALS['app']->time;
			}
		}else{
			$pay_date = $GLOBALS['app']->time;
		}
		$price = $GLOBALS['app']->get('mc_gross');
		$array = array('pay_status'=>"付款完成",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
		$array['status'] = '付款完成';
		$exchange_rate = $GLOBALS['app']->get('exchange_rate');
		if($exchange_rate){
			$array['pay_currency_rate'] = $exchange_rate;
		}
		$p_array = array();
		$p_array['txn_id'] = $GLOBALS['app']->get('txn_id');
		$p_array['txn_type'] = $GLOBALS['app']->get('txn_type');
		$p_array['mc_fee'] = $GLOBALS['app']->get('mc_fee');
		$p_array['mc_currency'] = $GLOBALS['app']->get('mc_currency');
		$p_array['payer_email'] = $GLOBALS['app']->get('payer_email');
		$p_array['first_name'] = $GLOBALS['app']->get('first_name');
		$p_array['last_name'] = $GLOBALS['app']->get('last_name');
		$p_array['payer_business_name'] = $GLOBALS['app']->get('payer_business_name');
		$p_array['payer_status'] = $GLOBALS['app']->get('payer_status');
		$array['ext'] = serialize($p_array);
		$GLOBALS['app']->model('order')->save($array,$this->order['id']);
		exit('SUCCESS');
	}
}
?>