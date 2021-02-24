<?php
/**
 * 汉特支付公共类
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月9日
**/

class hantepay
{
	private $apikey = '';
	private $merchant_no = '';
	private $store_no = '';
	private $currency = 'USD'; //货币符号
	private $price = '0.00'; //结算金额，仅支持美元
	private $price_CNY = '0.00'; //结算金额，人民币，注意，如果使用CNY，系统会自动跟据汇率自动转成当前对应的美元
	private $notify_url; //异步通知地址
	private $notice_url; //同步通知地址
	private $payment_method = 'alipay'; //默认使用支付宝支付
	private $terminal = 'ONLINE'; //终端，ONLINE 指PC，WAP 指手机
	private $paytype = 'securepay'; //标准支付
	private $sn = ''; //订单编号
	private $note = ''; // 备注，会原路返回
	private $body;//订单信息

	public function __construct($config = array())
	{
		if($config && is_array($config) && count($config)>0){
			$this->config($config);
		}
	}

	/**
	 * 密钥
	 * @参数 $val 汉特支付提供的密钥，一般是32位 
	**/
	public function apikey($val='')
	{
		if($val != ''){
			$this->apikey = $val;
		}
		return $apikey;
	}

	public function body($val='')
	{
		if($val != ''){
			$this->body = $val;
		}
		return $this->body;
	}
	
	/**
	 * 校验请求返回参数
	**/
	public function check($data)
	{
		if(!$data || !is_array($data) || !$data['signature']){
			return false;
		}
		$sign=$this->signature($data);
		if($sign == $data['signature']){
			return true;
		}
		return false;
	}

	public function config($config,$val='')
	{
		if($config && is_array($config) && count($config)>0){
			foreach($config as $key=>$value){
				$this->$key = $value;
			}
		}
		if($config && is_string($config) && $val !=''){
			$this->$config = $val;
		}
		if($config && is_string($config) && $val == ''){
			return $this->$config;
		}
		return false;
	}

	public function curl($url,$post=array(),$header=array())
	{
		global $app;
		foreach($header as $key=>$value){
			$app->lib('curl')->set_header($key,$value);
		}
		if($post && count($post)>0){
			$app->lib('curl')->is_post(true);
			$app->lib('curl')->post_data($app->lib('json')->encode($post));
		}
		$info = $app->lib('curl')->get_json($url);
		if(!$info){
			return false;
		}
		return $info;
	}

	public function curl_get($url,$post=array())
	{
		$url .= "?";
		foreach($post as $key=>$value){
			$url .= $key."=".rawurlencode($value)."&";
		}
		$url = trim($url,'&');
		global $app;
		$info = $app->lib('curl')->get_json($url);
		if(!$info){
			$this->error('获取失败');
		}
		return $info;
	}

	public function currency($val='')
	{
		if($val != ''){
			$this->currency = $val;
		}
		return $this->currency;
	}

	public function error($info='',$code='')
	{
		global $app;
		$tip = $code ? $code.': '.$info : $info;
		$app->error($tip);
	}

	public function is_mobile($val=false)
	{
		return $this->terminal($val);
	}

	public function is_pc($val=false)
	{
		$obj = $val ? false : true;
		return $this->terminal($obj);
	}

	public function merchant_no($val='')
	{
		if($val != ''){
			$this->merchant_no = $val;
		}
		return $this->merchant_no;
	}

	/**
	 * 随机数生成
	**/
	public function nonce_str()
	{
		return md5(uniqid(microtime(true),true));
	}

	public function note($val='')
	{
		if($val != ''){
			$this->note = $val;
		}
		return $this->note;
	}

	public function notice_url($val='')
	{
		if($val != ''){
			$this->notice_url = $val;
		}
		return $this->notice_url;
	}

	public function notify_url($val='')
	{
		if($val != ''){
			$this->notify_url = $val;
		}
		return $this->notify_url;
	}

	//格式化参数格式化成url参数
	public function params2str($data)
	{
		$buff = "";
		foreach ($data as $k => $v) {
			if ($k != "signature" && $v !== "" && !is_array($v)) {
				$buff .= $k."=".$v."&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}

	public function payment_method($val='')
	{
		if($val != '' && in_array($val,array('wechatpay','alipay'))){
			$this->payment_method = $val;
		}
		return $this->payment_method;
	}

	public function paytype($val='')
	{
		if($val != ''){
			$this->paytype = $val;
		}
		return $this->paytype;
	}

	public function price($val='')
	{
		if($val != ''){
			$this->price = $val;
		}
		return $this->price;
	}

	public function price_CNY($val='')
	{
		if($val != ''){
			$this->price_CNY = $val;
		}
		return $this->price_CNY;
	}

	public function query($sn)
	{
		$formurl = 'https://gateway.hantepay.com/v2/gateway/orderquery'; //安全支付
		$data = array();
		$data['merchant_no'] = $this->merchant_no;
		$data['store_no'] = $this->store_no;
		$data['sign_type'] = 'MD5';
		$data['nonce_str'] = $this->nonce_str();
		$data['time'] = time();
		$data['out_trade_no'] = $sn;
		$data['signature'] = $this->signature($data);
		//$header = array('Accept'=>"application/json","Content-Type"=>"application/json");
		$info = $this->curl_get($formurl,$data);
		if(!$info){
			$this->error('支付失败，未获取到远程信息');
		}
		if($info['return_code'] == 'ok' && $info['result_code'] == 'SUCCESS'){
			return $info['data'];
		}
		$this->error($info['return_msg'],$info['result_code']);
	}

	//退款操作
	public function refund($formdata)
	{
		$formurl = 'https://gateway.hantepay.com/v2/gateway/refund';
		$data = array();
		$data['merchant_no'] = $this->merchant_no;
		$data['store_no'] = $this->store_no;
		$data['sign_type'] = 'MD5';
		$data['nonce_str'] = $this->nonce_str();
		$data['time'] = time();
		$data['currency'] = 'USD';
		$data['out_trade_no'] = $formdata['out_trade_no'];
		$data['transaction_id'] = $formdata['transaction_id'];
		$data['refund_no'] = $formdata['refund_no'];
		if($formdata['refund_rmb_amount']){
			$data['refund_rmb_amount'] = $formdata['refund_rmb_amount'];
		}else{
			$data['refund_amount'] = $formdata['refund_amount'];
		}
		$data['refund_desc'] = $formdata['refund_desc'];
		$data['signature'] = $this->signature($data);
		$header = array('Accept'=>"application/json","Content-Type"=>"application/json");
		$info = $this->curl($formurl,$data,$header);
		if(!$info){
			return false;
		}
		return $info;
	}


	public function signature($data=array())
	{
		if(!$data || !is_array($data)){
			$data = array();
		}
		if(array_key_exists('sign_type',$data)){
			unset($data['sign_type']);
		}
		if(array_key_exists('signature',$data)){
			unset($data['signature']);
		}
		ksort($data);
		$string=$this->params2str($data).'&'.$this->apikey;
		$string=md5($string);
		return strtolower($string);
	}

	public function sn($val='')
	{
		if($val != ''){
			$this->sn = $val;
		}
		return $this->sn;
	}

	public function store_no($val='')
	{
		if($val != ''){
			$this->store_no = $val;
		}
		return $this->store_no;
	}

	/**
	 * 解析参数信息，返回参数数组
	 */
	public function str2params($query)
	{
		if(!$query || !is_string($query) || strpos($query,'&') !== true){
			return false;
		}
		parse_str($query,$list);
		return $list;
	}

	/**
	 * 提交操作
	**/
	public function submit()
	{
		$formurl = 'https://gateway.hantepay.com/v2/gateway/'.$this->paytype; //安全支付
		$data = array();
		$data['merchant_no'] = $this->merchant_no;
		$data['store_no'] = $this->store_no;
		$data['sign_type'] = 'MD5';
		$data['nonce_str'] = $this->nonce_str();
		$data['time'] = time();
		$data['currency'] = 'USD';
		$data['out_trade_no'] = $this->sn;
		if($this->price_CNY > 0){
			$data['rmb_amount'] = $this->price_CNY;
		}else{
			$data['amount'] = $this->price;
		}
		$data['body'] = $this->body;
		$data['notify_url'] = $this->notify_url;
		if($this->note){
			$data['note'] = $this->note;
		}
		if($this->paytype == 'securepay'){
			$data['payment_method'] = $this->payment_method;
			$data['terminal'] = $this->terminal;
			$data['callback_url'] = $this->notice_url;
		}
		if($this->paytype == 'qrcode'){
			$data['payment_method'] = $this->payment_method;
		}
		$data['signature'] = $this->signature($data);
		$header = array('Accept'=>"application/json","Content-Type"=>"application/json");
		$info = $this->curl($formurl,$data,$header);
		if(!$info){
			$this->error('支付失败，未获取到远程信息');
		}
		if($info['return_code'] == 'ok' && $info['result_code'] == 'SUCCESS'){
			return $info['data'];
		}
		$this->error($info['return_msg'],$info['result_code']);
	}


	public function terminal($is_mobile=false)
	{
		if($is_mobile){
			$this->terminal = 'WAP';
		}else{
			$this->terminal = 'ONLINE';
		}
		return $this->terminal;
	}
}