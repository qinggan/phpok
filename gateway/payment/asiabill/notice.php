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
class asiabill_notice
{
	public $paydir;
	public $order;
	public $payment;
	private $config;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/asiabill/';
		$this->baseurl = $GLOBALS['app']->url;
		if($this->param['param']){
			$this->config = array('mer_no'=>$this->param['param']["mer_no"]);
			$this->config['gateway_no'] = $this->param['param']["gateway_no"];
			$this->config['sign_key'] = $this->param['param']["sign_key"];
			$this->config['action_url'] = $this->param['param']['action'];
			$this->config['utype'] = $this->param['param']['utype'];
		}
		include_once($this->paydir."lib/asiabill.php");
	}

	public function submit()
	{
		global $app;
		if($this->order['status']){
			return true;
		}
		$obj = new asiabill_payment($this->config);
		$tradeNo = $app->get('tradeNo');
		$sn = $app->get('orderNo');
		$currency = $app->get('orderCurrency');
		$price = $app->get('orderAmount');
		$orderStatus = $app->get('orderStatus');
		$orderInfo = $app->get('orderInfo');
		if(!$orderStatus){
			$app->error($orderInfo);
			return false;
		}
		$obj->params('trade_no',$tradeNo);
		$obj->params('sn',$sn);
		$obj->params('currency',$currency);
		$obj->params('price',$price);
		$obj->params('status',$orderStatus);
		$obj->params('info',$orderInfo);
		$sign = $app->get('signInfo');
		$chk = $obj->sha256check($sign);
		if(!$chk){
			phpok_log('ASIABill签名验证不过关');
			return false;
		}
		$p_array = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		if(isset($_POST)){
			$p_array = array_merge($p_array,$_POST);
		}
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