<?php
/**
 * 退款操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class alipay_refund
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	private $alipay;
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
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/alipay/';
		$this->alipay = $GLOBALS['app']->lib('alipay');
		$this->baseurl = $GLOBALS['app']->url;
	}

	public function param($param)
	{
		$this->param = $param;
	}

	public function order($order)
	{
		$this->order = $order;
	}

	public function refund_id($refund_id=0)
	{
		if($refund_id){
			$this->refund_id = $refund_id;
		}
		return $this->refund_id;
	}

	public function notify()
	{
		global $app;
		unset($_GET[$app->config['ctrl_id']],$_GET[$app->config['func_id']],$_GET['sn'],$_GET['_noCache']);
		$alipay_config = array('partner'=>$this->param['param']['pid'],'key'=>$this->param['param']['key']);
		$alipay_config['sign_type'] ='MD5';
		$alipay_config['input_charset']= 'utf-8';
		$alipay_config['cacert']    = $this->paydir.'cacert.pem';
		$alipay_config['transport']    = 'http';
		$this->alipay->config($alipay_config);
		$flag = $this->alipay->verify($_GET);
		if(!$flag){
			exit('fail');
		}
		$data = array('status'=>1);
		$data['ext'] = ($this->refund['ext'] && is_string($this->refund['ext'])) ? unserialize($this->refund['ext']) : ($this->refund['ext'] ? $this->refund['ext'] : array());
		$data['ext'] = array_merge($_GET,$data['ext']);
		$data['ext'] = serialize($data['ext']);
		$app->model('order')->refund_save($data,$this->refund['id']);
		exit('success');
	}

	//退款操作
	public function submit()
	{
		global $app;
		if(!$this->order['ext']['trade_no']){
			$app->error(P_Lang('未找到支付宝订单号，请检查'));
		}
		$order = $app->model('order')->get_one($this->order['order_id']);
		if(!$order){
			$app->error(P_Lang('未找到订单信息'));
		}
		$form_url = 'https://mapi.alipay.com/gateway.do';
		if($this->param && $this->param['param'] && $this->param['param']['envtype'] && $this->param['param']['envtype'] == 'demo'){
			$form_url = 'https://mapi.alipaydev.com/gateway.do';
		}
		if($this->param && $this->param['param'] && $this->param['param']['envtype'] && $this->param['param']['envtype'] == 'product_n'){
			$form_url = 'https://intlmapi.alipay.com/gateway.do';
		}
		$currency_id = $currency_code = '';
		if($this->param['currency'] && is_string($this->param['currency']) && !is_numeric($this->param['currency'])){
			$currency_code = $this->param['currency'];
		}
		if($this->param['currency'] && is_numeric($this->param['currency'])){
			$currency_id = $this->param['currency'];
		}
		if($this->param['currency'] && is_array($this->param['currency'])){
			$currency_id = $this->param['currency']['id'];
		}
		if(!$currency_id && !$currency_code){
			$currency_id = $this->order['currency_id'];
		}
		if(!$currency_id && !$currency_code){
			$this->error(P_Lang('异常，未指定货币'));
		}
		if($currency_id){
			$currency = $app->model('currency')->get_one($currency_id);
		}else{
			$currency = $app->model('currency')->get_one($currency_code,'code');
		}
		
		//跳转退款（即时到账）
		if($this->param['param']['prikey'] && $this->param['param']['pubkey'] && $this->param['param']['ptype'] == 'pagepay'){
			$return_url = $app->url('order','refund','id='.$this->refund_id,'www',true);
			$this->alipay->gateway_url($form_url);
			$this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$this->alipay->return_url($return_url);
			$params = array();
			$params['trade_no'] = $this->order['ext']['trade_no'];
			$params['sn'] = $order['sn'];
			$params['price'] = $this->refund['price'];
			$params['currency'] = $currency['code'];
			$params['note'] = $this->refund['note'];
			$this->alipay->refund_page($params);
		}
		//即时到账有密退款
		if($this->param['param']['ptype'] == 'create_direct_pay_by_user'){
			$notify_url = $this->baseurl."gateway/payment/alipay/notify_url.php";
	        $alipay_config = array('partner'=>$this->param['param']['pid'],'key'=>$this->param['param']['key']);
			$alipay_config['sign_type'] ='MD5';
			$alipay_config['input_charset']= 'utf-8';
			$alipay_config['cacert']    = $this->paydir.'cacert.pem';
			$alipay_config['transport']    = 'http';
			$this->alipay->config($alipay_config);
			$parameter = array(
				"service" => 'refund_fastpay_by_platform_pwd',
				"partner" => trim($this->param['param']['pid']),
				"_input_charset"	=> 'utf-8',
				"payment_type"	=> 1,
				"notify_url"	=> $notify_url,
				"seller_user_id"	=> $this->order['ext']['seller_id'],
				"refund_date"	=> date("Y-m-d H:i:s",$this->refund['dateline']),
				"batch_no"	=> $this->refund['sn'],
				"batch_num"	=> 1,
				"detail_data"	=> $this->order['ext']['trade_no'].'^'.$this->refund['price'].'^'.$this->refund['note']
			);
			$params = $this->alipay->params($parameter);
			$this->alipay->submit($params,$form_url);
			echo "<pre>".print_r(1233,true)."</pre>";
		}
		//订单退款
		if($this->param['param']['prikey'] && $this->param['param']['pubkey']){
			$this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$this->alipay->return_url($return_url);
			$params = array();
			$params['trade_no'] = $this->order['ext']['trade_no'];
			$params['sn'] = $order['sn'];
			$params['price'] = $this->refund['price'];
			$params['note'] = $this->refund['note'];
			$data = $this->alipay->refund($params);
			if(!$data){
				$this->error(P_Lang('退款失败，请检查'));
			}
			$t = $data->alipay_trade_refund_response;
			if($t->code != '10000'){
				$info = $t->msg.'('.$t->code.')';
				if($t->sub_msg){
					$info .= ' '.$t->sub_msg;
					if($t->sub_code){
						$info .= '('.$t->sub_code.')';
					}
				}
				//删除退款记录
				if($this->refund && $this->refund['id']){
					$app->model('order')->refund_delete($this->refund['id']);
				}
				$this->error($info);
			}
			//保存退款记录（未测试）
			if($this->refund && $this->refund['id']){
				$tmp = array('status'=>1);
				$tmp['ext'] = ($this->refund['ext'] && is_string($this->refund['ext'])) ? unserialize($this->refund['ext']) : ($this->refund['ext'] ? $this->refund['ext'] : array());
				$tinfo = $app->lib('json')->encode($t);
				$t = $app->lib('json')->decode($tinfo);
				$tmp['ext'] = array_merge($t,$tmp['ext']);
				$tmp['ext'] = serialize($tmp['ext']);
				$app->model('order')->refund_save($tmp,$this->refund['id']);
			}
			$this->success();
		}
	}
}