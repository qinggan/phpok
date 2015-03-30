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
		$this->paydir = $GLOBALS['app']->dir_root.'payment/paypal/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."lib/paypal.php");
	}

	function submit()
	{
		if($_SESSION['user_id']){
			$url = $GLOBALS['app']->url('order','info','id='.$_GET['id']);
			unset($_GET['id']);
		}else{
			$url = $GLOBALS['app']->url('order','info','sn='.$_GET['sn'].'&passwd='.$_GET['passwd']);
			unset($_GET['sn'],$_GET['passwd']);
		}
		$paypal = new paypal_payment($this->param['param']["payid"],$this->param['param']["at"]);
		$paypal->set_value("action_url",$this->param['param']["action"]);
		$price = $GLOBALS['app']->get('mc_gross');
		$sn = $GLOBALS['app']->get('invoice');
		$checkcode = $GLOBALS['app']->get('custom');
		if(!$checkcode || !$price || !$sn){
			error(P_Lang('支付返回异常，请检查'),$url,'notice');
		}
		$chk = $paypal->check($price,$sn,$checkcode);
		if(!$chk){
			error(P_Lang('数据验证不通过，请检查'),$url,'notice');
		}
		$ext = $this->order['ext'];
		if($ext && is_string($ext)){
			$ext = unserialize($this->order['ext']);
		}
		if($ext && is_array($ext)){
			if($ext['txn_id'] && $ext['txn_id'] == $GLOBALS['app']->get('txn_id')){
				error(P_Lang('订单支付成功'),$url,'ok');
			}
		}
		$payment_status = $GLOBALS['app']->get('payment_status');
		if($payment_status != 'Completed'){
			error(P_Lang('支付不确定是否完成，请联系商家确认'),$url,'notice');
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
		return true;
	}
}
?>