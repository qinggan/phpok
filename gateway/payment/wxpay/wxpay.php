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
	private $app_id = '';
	private $mch_id = '';
	private $app_key = '';
	private $app_secret = '';
	private $ssl_cert = '';
	private $ssl_key = '';
	private $proxy_host = '0.0.0.0';
	private $proxy_port = '';
	private $nonce_str = '';
	private $timeout = 6;
	public function __construct()
	{
		$this->nonce_str = strtoupper(md5(time().'-'.rand(100,999).'-phpok'));
	}

	//定义公众账号ID
	public function app_id($app_id='')
	{
		$this->app_id = $app_id;
	}

	public function mch_id($mch_id='')
	{
		$this->mch_id = $mch_id;
	}

	public function app_key($key='')
	{
		$this->app_key = $key;
	}

	public function app_secret($secret='')
	{
		$this->app_secret = $secret;
	}

	public function ssl_cert($file='')
	{
		$this->ssl_cert = $file;
	}

	public function ssl_key($file='')
	{
		$this->ssl_key = $file;
	}

	public function proxy_host($host='')
	{
		$this->proxy_host = $host;
	}

	public function proxy_port($port='')
	{
		$this->proxy_port = $port;
	}

	public function timeout($time=10)
	{
		$this->timeout = $time;
	}

	public function query($sn)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		if(!$sn){
			return false;
		}
		$data = array('appid'=>$this->app_id,'mch_id'=>$this->mch_id,'nonce_str'=>$this->nonce_str);
		$data['out_trade_no'] = $sn;
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		$sign = $rs['sign'];
		$chksign = $this->create_sign($rs);
		if($sign == $chksign){
			return $rs;
		}
		return false;
	}

	public function pay_url($data)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		if(!$data['out_trade_no'] || !$data['total_fee'] || !$data['trade_type'] || !$data['body']){
			return false;
			//return array('status'=>'error','content'=>'参数不完整');
		}
		if($data['trade_type'] == 'NATIVE' && !$data['product_id']){
			return false;
			//return array('status'=>'error','content'=>'统一支付接口中，缺少必填参数product_id！trade_type为NATIVE时，product_id为必填参数');
		}
		if($data['trade_type'] == "JSAPI" && !$data['openid']){
			return false;
			//throw new WxPayException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
		}
		$data['appid'] = $this->app_id;
		$data['mch_id'] = $this->mch_id;
		$data['nonce_str'] = $this->nonce_str;
		$data['spbill_create_ip'] = $GLOBALS['app']->lib('common')->ip();
		$data['time_start'] = date("YmdHis",time());
		$data['time_expire'] = date("YmdHis",time() + 600);
		//$url.= $this->array_to_string($data);
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		//$url .= "&sign=".$sign;

		$xml = $this->ToXml($data);
		$startTimeStamp = $this->getMillisecond();//请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		$sign = $rs['sign'];
		$chksign = $this->create_sign($rs);
		if($sign == $chksign){
			return $rs['code_url'];
		}
		return false;
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

	private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		if($this->proxy_host && $this->proxy_host != '0.0.0.0' && $this->proxy_port){
			curl_setopt($ch,CURLOPT_PROXY,$this->proxy_host);
			curl_setopt($ch,CURLOPT_PROXYPORT,$this->proxy_port);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($useCert == true){
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $this->ssl_cert);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $this->ssl_key);
		}
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

}
?>