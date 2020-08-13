<?php
/**
 * 异步推送通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年3月24日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class allinpay_notify
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
			exit('SUCCESS');
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
			phpok_log($data['outTransNo'].'_'.$data['resultCode'].'_'.$data['resultMsg']);
			exit($data['resultMsg']);
		}
		$obj = new allinpay_payment($this->config);
		$signature = $app->get('signature');
		if(!$signature){
			phpok_log($data['outTransNo'].'_签名丢失');
			exit($data['outTransNo'].'_签名丢失');
		}
		
		if(!$obj->sha256check($data,$signature)){
			phpok_log($data['outTransNo'].'_签名不通过');
			exit($data['outTransNo'].'_签名不通过');
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
		exit('SUCCESS');
	}
}