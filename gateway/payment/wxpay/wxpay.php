<?php
/*****************************************************************************************
	文件： gateway/payment/wxpay/wxpay.php
	备注： 微信支付类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月04日 14时22分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wxpay_lib
{
	private $appid = '';
	private $sub_appid = '';
	private $mch_id = '';
	private $sub_mch_id = '';
	private $app_key = '';
	private $app_secret = '';
	private $pem_cert = '';
	private $pem_key = '';
	private $pem_ca = '';
	private $proxy_host = '0.0.0.0';
	private $proxy_port = 0;
	private $nonce_str = '';
	private $timeout = 6;
	private $errmsg = '';
	private $trade_type = 'native';
	private $red_config = array();
	private $timeStamp = '';
	
	public function __construct()
	{
		$this->nonce_str = md5(time().'-'.rand(100,999).'-phpok');
		$this->time_stamp = time();
	}

	public function config($config,$id='')
	{
		if($config && is_array($config)){
			foreach($config as $key=>$value){
				if($value != ''){
					$this->$key = $value;
				}
			}
		}else{
			if($config && $id){
				$this->$id = $config;
			}
		}
	}

	//定义公众账号ID
	public function appid($val='')
	{
		if($val){
			$this->appid = $val;
		}
		return $this->appid;
	}

	public function sub_appid($val='')
	{
		if($val){
			$this->sub_appid = $val;
		}
		return $this->sub_appid;
	}

	public function nonce_str()
	{
		return $this->nonce_str;
	}

	public function mch_id($val='')
	{
		if($val){
			$this->mch_id = $val;
		}
		return $this->mch_id;
	}

	public function sub_mch_id($val='')
	{
		if($val){
			$this->sub_mch_id = $val;
		}
		return $this->sub_mch_id;
	}

	public function app_key($val='')
	{
		if($val){
			$this->app_key = $val;
		}
		return $this->app_key;
	}

	public function app_secret($val='')
	{
		if($val){
			$this->app_secret = $val;
		}
		return $this->app_secret;
	}

	public function pem_cert($val='')
	{
		if($val){
			$this->pem_cert = $val;
		}
		return $this->pem_cert;
	}

	public function pem_key($val='')
	{
		if($val){
			$this->pem_key = $val;
		}
		return $this->pem_key;
	}

	public function pem_ca($val='')
	{
		if($val){
			$this->pem_ca = $val;
		}
		return $this->pem_ca;
	}

	public function proxy_host($val='')
	{
		if($val){
			$this->proxy_host = $val;
		}
		return $this->proxy_host;
	}

	public function proxy_port($val='')
	{
		if($val){
			$this->proxy_port = $val;
		}
		return $this->proxy_port;
	}

	public function trade_type($val='')
	{
		if($val){
			$this->trade_type = $val;
		}
		return $this->trade_type;
	}

	public function timeout($val=0)
	{
		if($val){
			$this->timeout = $val;
		}
		return $this->timeout;
	}

	public function time_stamp($time='')
	{
		if($time){
			$this->time_stamp = $time;
		}
		return $this->time_stamp;
	}

	public function errmsg($val='')
	{
		if($val){
			$this->errmsg = $val;
			$this->_log($val);
		}
		return $this->errmsg;
	}

	public function get_openid()
	{
		if (!isset($_GET['code'])){
			$baseUrl = urlencode('https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			header("Location: $url");
			exit;
		}else{
		    $code = $_GET['code'];
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}

	/**
	 * 
	 * 构造获取code的url连接
	 * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	 * @return 返回构造好的url
	 */
	private function __CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}


	/**
	 * 
	 * 通过code从工作平台获取openid机器access_token
	 * @param string $code 微信跳转回来带上的code
	 * 
	 * @return openid
	 */
	public function GetOpenidFromMp($code)
	{
		$url = $this->__CreateOauthUrlForOpenid($code);
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->proxy_host != "0.0.0.0" && $this->proxy_port){
			curl_setopt($ch,CURLOPT_PROXY,$this->proxy_host);
			curl_setopt($ch,CURLOPT_PROXYPORT,$this->proxy_port);
		}
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->data = $data;
		$openid = $data['openid'];
		return $openid;
	}

	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["secret"] = $this->app_secret;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}

	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v){
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}


	public function query($sn)
	{
		if(!$sn){
			$this->errmsg('未指定订单编号');
			return false;
		}
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		$data = array('appid'=>$this->appid,'mch_id'=>$this->mch_id,'nonce_str'=>$this->nonce_str);
		$data['out_trade_no'] = $sn;
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		return $rs;
	}

	public function getIp()
	{
		if(!empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else if(!empty($_SERVER["REMOTE_ADDR"]))
		{
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		else
		{
			$cip = '';
		}
		preg_match("/[\d\.]{7,15}/", $cip, $cips);
		$cip = isset($cips[0]) ? $cips[0] : 'unknown';
		unset($cips);
		return $cip;
	}

	//创建订单
	public function create($data)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		if(!$data['out_trade_no'] || !$data['total_fee'] || !$data['body']){
			$this->errmsg('参数不完整：价格，订单号，订单内容');
			return false;
		}
		if($this->trade_type == 'native' && !$data['product_id']){
			$this->errmsg('统一支付接口中，缺少必填参数product_id！trade_type为NATIVE时，product_id为必填参数');
			return false;
		}
		if($this->trade_type == 'jsapi' && !$data['openid']){
			$this->errmsg('统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！');
			return false;
		}
		if($this->appid){
			$data['appid'] = $this->appid;
		}
		$data['mch_id'] = $this->mch_id;
		$data['nonce_str'] = $this->nonce_str;
		$data['spbill_create_ip'] = $this->getIp();
		if(!$data['spbill_create_ip']){
			$data['spbill_create_ip'] = '0.0.0.0';
		}
		$data['trade_type'] = strtoupper($this->trade_type());
		if($this->sub_mch_id){
			$data['sub_mch_id'] = $this->sub_mch_id;
		}
		if($this->sub_appid){
			$data['sub_appid'] = $this->sub_appid;
		}
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			$this->_log($rs);
			return false;
		}
		$sign = $rs['sign'];
		$chksign = $this->create_sign($rs);
		if($sign == $chksign){
			return $rs;
		}
		return false;
	}

	private function _log($info)
	{
		if(is_array($info) || is_object($info)){
			$info = print_r($info,true);
		}
		if(!$info){
			$info = time();
		}
		phpok_log($info);
		return true;
	}

	public function FromXml($xml)
	{	
		if(!$xml){
			return false;
		}
		return $GLOBALS['app']->lib('xml')->read($xml,false);
	}

	public function ToXml($data)
	{
		if(!is_array($data) || count($data) <= 0){
			return false;
    	}
    	
    	$xml = "<xml>";
    	foreach ($data as $key=>$val){
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}


	public function create_sign($array)
	{
		ksort($array);
		$string = $this->array_to_string($array);
		$string = $string . "&key=".$this->app_key;
		$string = md5($string);
		$result = strtoupper($string);
		return $result;
	}

	private function array_to_string($list)
	{
		$buff = "";
		foreach ($list as $k => $v){
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		if($this->proxy_host && $this->proxy_host != '0.0.0.0' && $this->proxy_port){
			curl_setopt($ch,CURLOPT_PROXY,$this->proxy_host);
			curl_setopt($ch,CURLOPT_PROXYPORT,$this->proxy_port);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new Exception("curl出错，错误码：".$error);
		}
	}

	private function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}


	public function GetJsApiParameters($data)
	{
		if(!array_key_exists("appid", $data) || !array_key_exists("prepay_id", $data) || $data['prepay_id'] == ""){
			$this->errmsg('参数错误');
			return false;
		}
		$values = array();
		$time = time();
		$values['appId'] = $data['appid'];
		$values['timeStamp'] = $this->time_stamp;
		$values['nonceStr'] = $this->nonce_str;
		$values['package'] = "prepay_id=".$data['prepay_id'];
		$values['signType'] = 'MD5';
		$values['paySign'] = $this->create_sign($values);
		return json_encode($values);
	}

	public function get_jsapi_param($data)
	{
		if(!array_key_exists("appid", $data) || !array_key_exists("prepay_id", $data) || $data['prepay_id'] == ""){
			$this->errmsg('参数错误');
			return false;
		}
		$values = array();
		$time = time();
		$values['appId'] = $data['appid'];
		$values['timeStamp'] = $this->time_stamp;
		$values['nonceStr'] = $this->nonce_str;
		$values['package'] = "prepay_id=".$data['prepay_id'];
		$values['signType'] = 'MD5';
		$values['paySign'] = $this->create_sign($values);
		return $values;
	}

	//红包活动参数
	//支持参数有：act_name：活动名称，wishing：活动祝愿
	public function red_config($config='')
	{
		if($config && is_array($config)){
			$this->red_config = $config;
		}
		return $this->red_config;
	}

	//发送红包给商户
	//openid，目标ID
	//price，价格，单位是：分
	public function hongbao($openid,$price)
	{
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		$data = $this->red_config();
		$data["max_value"] = $price;
		$data["min_value"] = $price;
		$data['re_openid'] = $openid;
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, true,$this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		return true;
	}

	public function wxAppParameters($data)
	{
		if(!$data || !$data['appid'] || !$data['prepay_id']){
			$this->errmsg('参数错误');
			return false;
		}
		$values = array();
		$time = time();
		$values['appid'] = $data['appid'];
		$values['partnerid'] = $data['partnerid'];
		$values['prepayid'] = $data['prepay_id'];
		$values['package'] = "Sign=WXPay";
		$values['timestamp'] = $this->time_stamp;
		$values['noncestr'] = $this->nonce_str;
		$values['sign'] = $this->create_sign($values,true);
		return json_encode($values);
	}
}