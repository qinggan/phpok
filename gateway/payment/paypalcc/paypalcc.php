<?php
/**
 * Paypal信用卡支付操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年04月07日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class paypalcc_payment
{
	private $cc_data = array();
	private $currency = "USD";
	private $invoice = 0;
	private $api_version = '60.0';
	private $api_username;
	private $api_password;
	private $api_signature;
	private $server_url = 'https://api-3t.paypal.com/nvp';
	private $price;

	/**
	 * 构造函数
	**/
	public function __construct($act_type='product')
	{
		if($act_type == 'demo'){
			$this->server_url = 'https://api-3t.sandbox.paypal.com/nvp';
		}else{
			$this->server_url = 'https://api-3t.paypal.com/nvp';
		}
		$this->cc_data['countrycode'] = "CN";
	}

	/**
	 * 信用卡信息设置
	 * @参数 $name 参数名称（也支持数组写法），支持以下参数：
	 *             firstname 名
	 *             lastname 姓
	 *             cvv2 CVV2 号码（信用卡背面三位数）
	 *             type 类型
	 *             number 号码
	 *             expdate 过期时间
	 *             street 街道
	 *             city 城市
	 *             state 省份
	 *             zipcode 邮编
	 *             countrycode 国家编码
	 * @参数 $val 值
	**/
	public function cc($name,$val='')
	{
		if($name && is_array($name)){
			foreach($name as $key=>$value){
				$this->cc_data[$key] = $value;
			}
			return true;
		}
		if($name && $val == '' && isset($this->cc_data[$name])){
			return $this->cc_data[$name];
		}
		if($name && $val != ''){
			$this->cc_data[$name] = $val;
		}
		return false;
	}

	/**
	 * 价格
	 * @参数 $val 值，留空返回默认值
	**/
	public function price($val='')
	{
		if($val != ''){
			$this->price = $val;
		}
		return $this->price;
	}
	/**
	 * 货币
	 * @参数 $val 值，留空返回默认值
	**/
	public function currency($val='')
	{
		if($val != ''){
			$this->currency = $val;
		}
		return $this->currency;
	}

	/**
	 * 账号
	 * @参数 $val 值，留空返回默认值
	**/
	public function api_username($val='')
	{
		if($val != ''){
			$this->api_username = $val;
		}
		return $this->api_username;
	}

	/**
	 * 密码
	 * @参数 $val 值，留空返回默认值
	**/
	public function api_password($val='')
	{
		if($val != ''){
			$this->api_password = $val;
		}
		return $this->api_password;
	}

	/**
	 * 签名
	 * @参数 $val 值，留空返回默认值
	**/
	public function api_signature($val='')
	{
		if($val != ''){
			$this->api_signature = $val;
		}
		return $this->api_signature;
	}

	/**
	 * 版本
	 * @参数 $val 值，留空返回默认值
	**/
	public function api_version($val='')
	{
		if($val != ''){
			$this->api_version = $val;
		}
		return $this->api_version;
	}

	/**
	 * 付款地址
	 * @参数 $val 值，留空返回默认值
	**/
	public function server_url($val='')
	{
		if($val != ''){
			$this->server_url = $val;
		}
		return $this->server_url;
	}

	public function act_type($type="product")
	{
		if($act_type == 'demo'){
			$this->server_url = 'https://api-3t.sandbox.paypal.com/nvp';
		}else{
			$this->server_url = 'https://api-3t.paypal.com/nvp';
		}
		return true;
	}

	public function submit()
	{
		global $app;
		$post = array('METHOD'=>'doDirectPayment');
		$post['PAYMENTACTION'] = 'sale';
		$post['USER'] = $this->api_username;
		$post['PWD'] = $this->api_password;
		$post['SIGNATURE'] = $this->api_signature;
		$post['VERSION'] = $this->api_version;
		$post['AMT'] = $this->price;
		$post['CREDITCARDTYPE'] = $this->cc_data['type'];
		$post['ACCT'] = $this->cc_data['number'];
		$post['EXPDATE'] = $this->cc_data['expdate'];
		$post['CVV2'] = $this->cc_data['cvv2'];
		$post['FIRSTNAME'] = $this->cc_data['firstname'];
		$post['LASTNAME'] = $this->cc_data['lastname'];
		if($this->cc_data['street']){
			$post['STREET'] = $this->cc_data['street'];
		}
		if($this->cc_data['city']){
			$post['CITY'] = $this->cc_data['city'];
		}
		if($this->cc_data['state']){
			$post['STATE'] = $this->cc_data['state'];
		}
		if($this->cc_data['zipcode']){
			$post['ZIP'] = $this->cc_data['zipcode'];
		}
		if($this->cc_data['countrycode']){
			$post['COUNTRYCODE'] = $this->cc_data['countrycode'];
		}
		if($this->currency){
			$post['CURRENCYCODE'] = $this->currency;
		}
		if($this->invoice){
			$post['INVNUM'] = $this->invoice;
		}
		$app->lib('curl')->is_post(true);
		foreach($post as $key=>$value){
			$app->lib('curl')->post_data($key,$value);
		}
		$info = $app->lib('curl')->get_content($this->server_url);
		if(!$info){
			return false;
		}
		parse_str($info,$data);
		return $data;
	}
}