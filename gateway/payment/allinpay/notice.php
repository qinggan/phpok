<?php
/*****************************************************************************************
	文件： payment/asiabill/notice.php
	备注： 支付通知页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2019年5月17日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class allinpay_notice
{
	public $paydir;
	public $order;
	public $payment;
	private $config;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/allinpay/';
		$this->baseurl = $GLOBALS['app']->url;
		if($this->param['param']){
			$this->config = array('mch_no'=>$this->param['param']["mch_no"]);
			$this->config['access_code'] = $this->param['param']["access_code"];
			$this->config['private_key'] = $this->param['param']["private_key"];
			$this->config['public_key'] = $this->param['param']['public_key'];
			$this->config['utype'] = $this->param['param']['utype'];
			$this->config['wx_appid'] = $this->param['param']['wx_appid'];
			$this->config['institution'] = $this->param['param']['institution'];
			$this->config['env'] = $this->param['param']['env'];
		}
		include_once($this->paydir."allinpay.php");
	}

	public function submit()
	{
		global $app;
		if($this->order['status']){
			return true;
		}
		$data = array();
		$data['requestNo'] = $app->get('requestNo');
		$data['version'] = $app->get('version');
		$data['accessCode'] = $app->get('accessCode');
		$data['transType'] = $app->get('transType');
		$data['signType'] = $app->get('signType');
		$data['mchNo'] = $app->get('mchNo');
		$data['outTransNo'] = $app->get('outTransNo');
		$data['transAmount'] = $app->get('transAmount');
		$data['currency'] = $app->get('currency');
		$data['resultCode'] = $app->get('resultCode');
		$data['resultMsg'] = $app->get('resultMsg');
		$data['transNo'] = $app->get('transNo');
		$data['paymentAmount'] = $app->get('paymentAmount');
		$data['paymentCurrency'] = $app->get('paymentCurrency');
		$bankTradeNo = $app->get('bankTradeNo');
		if($bankTradeNo){
			$data['bankTradeNo'] = $bankTradeNo;
		}
		$payTime = $app->get('payTime');
		if($payTime){
			$data['payTime'] = $payTime;
		}
		$bankUserId = $app->get('bankUserId');
		if($bankUserId){
			$data['bankUserId'] = $bankUserId;
		}
		if($data['resultCode'] != '0000' && $data['resultCode'] != 'P000'){
			$app->error($data['resultCode']."：".$data['resultMsg']);
			return false;
		}
		$obj = new allinpay_payment($this->config);
		$signature = $app->get('signature');
		if(!$signature){
			$app->error($data['outTransNo']."：签名丢失");
			return false;
		}
		if(!$obj->sha256check($data,$signature)){
			$app->error($data['outTransNo']."：签名不通过");
			return false;
		}
		$p_array = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$p_array = array_merge($p_array,$data);
		$array = array('status'=>1,'ext'=>serialize($p_array));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($p_array));
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
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