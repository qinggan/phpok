<?php
/*****************************************************************************************
	文件： payment/unionpay/notify.php
	备注： 异步通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月30日 12时12分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class unionpay_notify
{
	private $paydir;
	private $order;
	private $payment;
	public function __construct($order,$payment)
	{
		$this->order = $order;
		$this->param = $payment;
		$this->paydir = $GLOBALS['app']->dir_root."gateway/payment/unionpay/";
		include_once($this->paydir."unionpay.php");
	}

	public function submit()
	{
		if($this->order['status']){
			return true;
		}
		global $app;
		$payment = new unionpay_lib();
		$payment->set_verify_id($app->dir_root.$this->param['param']['verify_cert_file']);
		$array = array($app->config['ctrl_id'],$app->config['func_id'],'_id');
		$params = $_GET;
		foreach($array as $key=>$value){
			unset($params[$value]);
		}
		if($params['respCode'] != '00'){
			exit('fail');
		}
		$chk = $payment->verify($params);
		if(!$chk){
			exit('fail');
		}
		$pay_date = $app->time;
		$price = round(($params['settleAmt']/100),2);
		$data = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$data['traceNo'] = $params['traceNo'];
		$data['traceTime'] = $params['traceTime'];
		$data['queryId'] = $params['queryId'];
		$data['currencyCode'] = $params['currencyCode'];
		$array = array('status'=>1,'ext'=>serialize($data));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					//增加order_payment
					$array = array('order_id'=>$order['id'],'payment_id'=>$this->param['id']);
					$array['title'] = $this->param['title'];
					$array['price'] = $price;
					$array['dateline'] = $app->time;
					$array['ext'] = serialize($data);
					$app->model('order')->save_payment($array,$payinfo['id']);
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge' && $data['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		exit('success');
	}
}