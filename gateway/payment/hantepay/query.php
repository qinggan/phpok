<?php
/**
 * 订单接口查询
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class hantepay_query
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/hantepay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once $this->paydir.'hantepay.class.php';
	}

	public function param($param)
	{
		$this->param = $param;
	}

	public function order($order)
	{
		$this->order = $order;
	}

	/**
	 * 查询订单是否存在
	**/
	public function submit()
	{
		global $app;
		//检查订单信息
		if($this->order['status']){
			$app->success();
		}
		$pay = new hantepay();
		$pay->merchant_no($this->param['param']['merchant_no']);
		$pay->store_no($this->param['param']['store_no']);
		$pay->apikey($this->param['param']['key_secret']);
		$info = $pay->query($this->order['sn'].'-'.$this->order['id']);
		if(!$info){
			$app->error(P_Lang('订单信息没有查到'));
		}
		if($info['trade_status'] != 'success'){
			$app->error($info['trade_status']);
		}
		$data = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$data['log_id'] = $this->order['id'];
		foreach($info as $key=>$value){
			$data[$key] = $value;
		}
		$array = array('status'=>1,'ext'=>serialize($data));
		if(!$this->order['status']){
			$array = array('status'=>1,'ext'=>serialize($data));
			$app->model('payment')->log_update($array,$this->order['id']);
		}
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
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge'){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		$app->success();
	}
}