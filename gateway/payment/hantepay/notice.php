<?php
/**
 * 同步通知操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class hantepay_notice
{
	private $paydir;
	private $order;
	private $payment;
	public function __construct($order,$param)
	{
		global $app;
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $app->dir_root.'gateway/payment/hantepay/';
		$this->baseurl = $app->url;
		include_once $this->paydir.'hantepay.class.php';
	}

	//获取订单信息
	public function submit()
	{
		global $app;
		//如果异步通知已通验证，同步通知就不需要再次验收
		if($this->order['status']){
			return true;
		}
		unset($_GET[$app->config['ctrl_id']],$_GET[$app->config['func_id']],$_GET['id'],$_GET['_noCache']);
		$pay = new hantepay();
		$pay->merchant_no($this->param['param']['merchant_no']);
		$pay->store_no($this->param['param']['store_no']);
		$pay->apikey($this->param['param']['key_secret']);
		$info = $pay->check($_GET);
		if(!$info){
			phpok_log('订单验证不通过，请检查');
			return false;
		}
		$info = $_GET;
		$data = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$data = array_merge($data,$info);
		$array = array('status'=>1,'ext'=>serialize($data));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		//如果当前支付操作是订单
		if($info['rmb_amount']){
			$price = round($info['rmb_amount']/100,2);
			$currency = $app->model('currency')->get_one('CNY','code');
			$currency_rate = $currency['val'];
			if($info['amount']){
				$currency_rate = round($info['amount']/$info['rmb_amount'],8);
			}
		}else{
			$price = round($info['amount']/100,2);
			$currency = $app->model('currency')->get_one('USD','code');
			$currency_rate = $currency['val'];
		}
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($data));
					$payment_data['price'] = $price; //登记实付金额
					$payment_data['currency_id'] = $currency['id']; //登记实付货币
					$payment_data['currency_rate'] = $currency_rate; //登记实付汇率
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		//充值操作
		if($this->order['type'] == 'recharge' && $data['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notify',$this->order['id']);
		return true;
	}
}