<?php
/**
 * Citcon 支付类接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年2月25日
**/

class citcon_payment
{
	//返回地址
	private $callback_url = '';
	//取消地址
	private $cancel_url = '';
	//信用卡信息
	private $cc_data = array();
	//Curl库
	private $curl;
	//货币符号
	private $currency = 'CNY';
	private $error_info = '';
	private $error_status = false;
	//付款失败地址，留空使用 cancel_url
	private $fail_url = '';
	//异步通知网址
	private $notify_url = '';
	//支付平台
	private $platform = 'generic';
	//价格
	private $price = 0;
	//服务器地址
	private $server_url = 'https://citconpay.com/';
	//订单
	private $sn = '';
	//付款成功的地址，留空使用 callback_url
	private $success_url = '';
	//令牌
	private $token = '';
	
	public function __construct($token='')
	{
		$this->token($token);
		$this->curl = $GLOBALS['app']->lib('curl');
	}

	public function callback_url($url='')
	{
		if($url){
			$this->callback_url = $url;
		}
		return $this->callback_url;
	}

	public function cancel_url($url='')
	{
		if($url){
			$this->cancel_url = $url;
		}
		return $this->cancel_url;
	}

	/**
	 * 信用卡信息
	**/
	public function cc($name='',$val='')
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
		return $this->cc_data;
	}

	/**
	 * 信用卡提交支付
	**/
	public function cc_submit()
	{
		global $app;
		$post = array('pin'=>$this->token);
		$post['card_number'] = $this->cc_data['number'];
		$post['exp_date'] = $this->cc_data['expdate'];
		$post['amount'] = round($this->price/100,2);
		$post['cvv2cvc2'] = $this->cc_data['cvv2'];
		$post['invoice_number'] = $this->sn;
		if($this->cc_data['zipcode']){
			$post['avs_zip'] = $this->cc_data['zipcode'];
		}
		if($this->cc_data['address']){
			$post['avs_address'] = $this->cc_data['address'];
		}
		$app->lib('curl')->is_post(true);
		$urlext = array();
		foreach($post as $key=>$value){
			$app->lib('curl')->post_data($key,$value);
			$urlext[] = $key."=".rawurlencode($value);
		}
		$url = $this->server_url;
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'chop/rest/ccauthonly';
		$info = $app->lib('curl')->get_content($url);
		if(!$info){
			return false;
		}
		parse_str($info,$data);
		return $data;
	}
	/**
	 * 3个字符的ISO货币代码
	**/
	public function currency($code='')
	{
		if($code){
			$this->currency = $code;
		}
		return $this->currency;
	}

	public function error_info($info='')
	{
		if($info){
			$this->error_info = $info;
		}
		return $this->error_info;
	}

	public function error_status($status=false)
	{
		if(is_bool($status)){
			$this->error_status = $status;
		}
		return $this->error_status;
	}

	/**
	 * 付款失败地址
	**/
	public function fail_url($url='')
	{
		if($url){
			$this->fail_url = $url;
		}
		if(!$this->fail_url){
			$this->fail_url = $this->cancel_url();
		}
		return $this->fail_url;
	}

	/**
	 * 异步通知网址
	**/
	public function notify_url($url='')
	{
		if($url){
			$this->notify_url = $url;
		}
		return $this->notify_url;
	}

	/**
	 * 支付平台
	**/
	public function platform($platform='')
	{
		if($platform){
			$this->platform = $platform;
		}
		return $this->platform;
	}

	/**
	 * 价格，如果传入的价格带有小数点，则输出不带小数点的值
	**/
	public function price($price='')
	{
		if($price && $price>0){
			if(strpos($price,'.') !== false){
				$price = round($price*100);
			}
			$this->price = $price;
		}
		return $this->price;
	}

	public function qr_link()
	{
		$this->curl->set_head('Authorization','Bearer '.$this->token);
		$this->curl->is_ssl(false);
		$this->curl->is_post(true);
		$this->curl->post_data('amount',$this->price);
		$this->curl->post_data('currency',$this->currency);
		$this->curl->post_data('vendor','generic');
		$this->curl->post_data('reference',$this->sn);
		$this->curl->post_data('ipn_url',$this->notify_url);
		$this->curl->post_data('callback_url',$this->callback_url);
		$this->curl->post_data('allow_duplicates','yes');
		$url = $this->server_url;
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'payment/pay_qr';
		return $this->curl->get_content($url);
	}

	public function query()
	{
		$this->curl->set_head('Authorization','Bearer '.$this->token);
		$this->curl->is_ssl(false);
		$this->curl->is_post(true);
		$this->curl->post_data('reference',$this->sn);
		$this->curl->post_data('inquire_method','real');
		$url = $this->server_url;
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'payment/inquire';
		return $this->curl->get_json($url);
	}

	/**
	 * 服务器地址
	**/
	public function server_url($url='')
	{
		if($url){
			$this->server_url = $url;
		}
		return $this->server_url;
	}

	/**
	 * 推送标记
	**/
	public function sign_ipn($post)
	{
		ksort($post);
	    $str = "";
	    foreach ($post as $key=>$value) {
	        $str .= $key."=".$value."&";
	    }
	    $str .= "token=".$this->token;
	    return md5($str);
	}

	/**
	 * 订单编号
	**/
	public function sn($sn='')
	{
		if($sn){
			$this->sn = $sn;
		}
		return $sn;
	}

	public function submit_chop()
	{
		$this->curl->set_head('Authorization','Bearer '.$this->token);
		$this->curl->is_ssl(false);
		$this->curl->is_post(true);
		$this->curl->post_data('amount',$this->price);
		$this->curl->post_data('currency',$this->currency);
		$this->curl->post_data('vendor',$this->platform);
		$this->curl->post_data('reference',$this->sn);
		$this->curl->post_data('ipn_url',$this->notify_url);
		$this->curl->post_data('callback_url',$this->callback_url);
		$this->curl->post_data('allow_duplicates','yes');
		$url = $this->server_url;
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'chop/chop';
		$data = $this->curl->get_json($url);
		if(!$data || !$data['result']){
			$this->error_status(true);
			$this->error_info('获取数据失败');
			return false;
		}
		if($data['result'] == 'success'){
			$this->error_status(false);
			return $data['url'];
		}
		$this->error_status(true);
		$this->error_info($data['error']);
		return false;
	}

	/**
	 * 生成跳转网址
	**/
	public function submit_link()
	{
		$this->curl->set_head('Authorization','Bearer '.$this->token);
		$this->curl->is_ssl(false);
		$this->curl->is_post(true);
		$this->curl->post_data('amount',$this->price);
		$this->curl->post_data('currency',$this->currency);
		$this->curl->post_data('vendor',$this->platform);
		$this->curl->post_data('reference',$this->sn);
		$this->curl->post_data('ipn_url',$this->notify_url);
		$this->curl->post_data('callback_url',$this->callback_url);
		$this->curl->post_data('allow_duplicates','yes');
		$url = $this->server_url;
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'payment/pay';
		return $this->curl->get_content($url);
	}

	/**
	 * 付款成功地址
	**/
	public function success_url($url='')
	{
		if($url){
			$this->success_url = $url;
		}
		if(!$this->success_url){
			$this->success_url = $this->callback_url();
		}
		return $this->success_url;
	}
	
	public function token($token='')
	{
		if($token){
			$this->token = $token;
		}
		return $this->token;
	}
}