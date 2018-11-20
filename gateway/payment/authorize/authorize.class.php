<?php
/**
 * Authorize 支付类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年08月21日
**/

class authorize_lib
{
	private $url = '';
	private $url_test = 'https://apitest.authorize.net/xml/v1/request.api';
	private $url_product = 'https://api.authorize.net/xml/v1/request.api';
	private $api_name = '';
	private $api_key = '';
	private $ref_id = '';
	private $amount = 0; //订单金额
	private $sandbox = false;
	private $request_data = array();
	
	public function __construct($name='',$key='',$test=false)
	{
		$this->api_name($name);
		$this->api_key($key);
		$this->sandbox($test);
		$this->request_data['transactionRequest'] = array();
		$this->request_data['transactionRequest']['transactionType'] = 'authCaptureTransaction';
	}

	public function config($name='',$key='')
	{
		$this->api_name($name);
		$this->api_key($key);
		$this->request_data['merchantAuthentication'] = array('name'=>$this->api_name,'transactionKey'=>$this->api_key);
	}

	public function act_type($val='')
	{
		if($val){
			$this->request_data['transactionRequest']['transactionType'] = $val;
		}
		return $this->request_data['transactionRequest']['transactionType'];
	}

	public function api_name($name='')
	{
		if($name){
			$this->api_name = $name;
		}
		return $this->api_name;
	}

	public function api_key($key='')
	{
		if($key){
			$this->api_key = $key;
		}
		return $this->api_key;
	}

	public function sandbox($test='')
	{
		if(isset($test) && is_bool($test)){
			$this->sandbox = $test;
		}
		$this->url = $this->sandbox ? $this->url_test : $this->url_product;
		return $this->sandbox;
	}

	public function url($url='')
	{
		if($url){
			$this->url = $url;
		}
		return $this->url;
	}

	/**
	 * 设置要Post的数据，仅限一级
	**/
	public function post($key,$data)
	{
		$this->request_data['transactionRequest'][$key] = $data;
	}

	public function unpost($key)
	{
		if(isset($this->request_data['transactionRequest'][$key])){
			unset($this->request_data['transactionRequest'][$key]);
		}
	}

	/**
	 * 订单编号，用于连接本地和远程的ID
	 * @参数 $id 唯一的订单编号
	**/
	public function ref_id($id='')
	{
		if($id){
			$this->ref_id = $id;
		}
		$this->request_data['refId'] = $this->ref_id;
		return $this->ref_id;
	}

	public function amount($price='')
	{
		if($price != ''){
			$this->amount = $price;
		}
		$this->request_data['transactionRequest']['amount'] = $this->amount;
		return $this->amount;
	}

	public function to_json()
	{
		ksort($this->request_data);
		$data = array('createTransactionRequest'=>$this->request_data);
		return json_encode($data,JSON_UNESCAPED_UNICODE);
	}
}