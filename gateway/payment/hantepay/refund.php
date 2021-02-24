<?php
/**
 * 退款操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class hantepay_refund
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	private $refund_id;
	public function __construct($order,$param,$refund)
	{
		if($param && $param['param'] && is_string($param['param'])){
			$param['param'] = unserialize($param['param']);
		}
		if($order && $order['ext'] && is_string($order['ext'])){
			$order['ext'] = unserialize($order['ext']);
		}
		$this->param = $param;
		$this->order = $order;
		$this->refund = $refund;
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

	public function refund($refund='')
	{
		if($refund){
			$this->refund = $refund;
		}
		return $this->refund;
	}

	//退款操作
	public function submit()
	{
		global $app;
		
		$tmp1 = $this->order['ext']['out_trade_no'];
		$tmp2 = $this->order['ext']['trade_no'];
		if(!$tmp1 || !$tmp2){
			$app->error(P_Lang('未找到汉特订单号，请检查'));
		}
		if(strpos($tmp1,'-') !== false){
			$out_trade_no = $tmp1;
			$transaction_id = $tmp2;
		}else{
			$out_trade_no = $tmp2;
			$transaction_id = $tmp1;
		}
		$order = $app->model('order')->get_one($this->order['order_id']);
		if(!$order){
			$app->error(P_Lang('未找到订单信息'));
		}
		$data = array();
		$data['out_trade_no'] = $out_trade_no;
		$data['transaction_id'] = $transaction_id;
		$data['refund_no'] = $this->refund['sn'];
		//只能基于人民币退款
		if($this->order['ext']['rmb_amount']){
			$currency = $app->model('currency')->get_one('CNY','code');
			$data['refund_rmb_amount'] = intval($this->refund['price']*100);
		}else{
			$currency = $app->model('currency')->get_one('USD','code');
			$data['refund_amount'] = intval($this->refund['price']*100);
		}
		$data['refund_desc'] = $this->refund['note'];
		$pay = new hantepay();
		$pay->merchant_no($this->param['param']['merchant_no']);
		$pay->store_no($this->param['param']['store_no']);
		$pay->apikey($this->param['param']['key_secret']);
		$info = $pay->refund($data);
		if(!$info){
			if($this->refund && $this->refund['id']){
				$app->model('order')->refund_delete($this->refund['id']);
			}
			$app->error(P_Lang('退款失败，远程接口获取不到数据'));
		}
		if($info['return_code'] == 'ok' && $info['result_code'] == 'SUCCESS'){
			if($this->refund && $this->refund['id']){
				$tmp = array('status'=>1);
				$tmp['ext'] = ($this->refund['ext'] && is_string($this->refund['ext'])) ? unserialize($this->refund['ext']) : ($this->refund['ext'] ? $this->refund['ext'] : array());
				$tmp['ext'] = array_merge($info['data'],$tmp['ext']);
				$tmp['ext'] = serialize($tmp['ext']);
				$app->model('order')->refund_save($tmp,$this->refund['id']);
			}
			$app->success('退款成功','javascript:window.close();void(0)');
		}
		if($this->refund && $this->refund['id']){
			$app->model('order')->refund_delete($this->refund['id']);
		}
		$app->error($info['result_code'].': '.$info['return_msg']);
	}
}