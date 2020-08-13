<?php
/**
 * 通华收银宝支付类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月17日
**/
class allinpay_payment
{
	private $config = array();
	private $params = array();
	private $server_url = 'https://oats.allinpay.com/gateway/pay/consumeTrans';
	private $bill_url = 'https://oats.allinpay.com/gateway/download/downloadbill';
	public function __construct($config = array())
	{
		if($config){
			$this->config($config);
		}
	}

	public function config($var,$val='')
	{
		if($var && is_array($var)){
			$config = array_merge($this->config,$var);
			$this->config = $config;
			if($var['env']){
				$this->set_env($var['env']);
			}
		}
		if($val != '' && $var){
			$this->config[$var] = $val;
			if($var == 'env'){
				$this->set_env($val);
			}
		}
		if($val == '' && is_string($var)){
			if(isset($this->config[$var])){
				return $this->config[$var];
			}
			return false;
		}
		return $this->config;
	}

	public function unified_order()
	{
		global $app;
		$data = $this->postdata();
		$sign = $this->signature($data);
		$data['signature'] = $sign;
		$app->lib('curl')->is_post(true);
		foreach($data as $key=>$value){
			$app->lib('curl')->post_data($key,$value);
		}
		$info = $app->lib('curl')->get_json($this->server_url);
		return $info;
	}

	public function create_html()
	{
		global $app;
		$data = $this->postdata();
		$sign = $this->signature($data);
		$html  = "<form method='post' name='allinpay_payment' id='allinpay_payment' action='".$this->server_url."' target='_top'>\n";
		foreach($data as $key=>$value){
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		}
		$html .= '<input type="hidden" name="signature" value="'.$sign.'" />'."\n";
		$html .= "</form>";
		$html .= '<script type="text/javascript">'."\n";
		$html .= 'document.getElementById("allinpay_payment").submit();'."\n";
		$html .= '</script>'."\n";
		return $html;
	}

	public function currency($val='')
	{
		if($val){
			$this->params['currency'] = $val;
		}
		return $this->params['currency'];
	}

	public function params($var,$val='')
	{
		if($var && is_array($var)){
			$config = array_merge($this->config,$var);
			$this->params = $config;
		}
		if($val != '' && $var){
			$this->params[$var] = $val;
		}
		if($val == '' && is_string($var)){
			if(isset($this->params[$var])){
				return $this->params[$var];
			}
			return false;
		}
		return $this->params;
	}

	private function postdata()
	{
		$data = array();
		$data['requestNo'] = $this->params['sn'];
		$data['version'] = '1.0';
		$data['accessCode'] = $this->config['access_code'];
		$data['transType'] = strtoupper($this->config['utype']);
		$data['signType'] = 'RSA2';
		$data['mchNo'] = $this->config['mch_no'];
		$data['outTransNo'] = $this->params['sn'];
		$data['transAmount'] = $this->params['price'];
		$data['currency'] = $this->params['currency'];
		$data['notifyUrl'] = $this->params['notify_url'];
		$data['goodsSubject'] = $this->params['sn'];
		//支付宝下必填
		if($this->config['utype'] == 'alipay_pcweb' || $this->config['utype'] == 'alipay_h5' || $this->config['utype'] == 'alipay_app'){
			$data['goodsDetail'] = $this->params['goods'];
			$data['referUrl'] = $this->params['refer_url'];
			$data['paymentInstitution'] = $this->config['institution'] == 'none' ? 'ALIPAYCN' : strtoupper($this->config['institution']);
			$data['returnUrl'] = $this->params['return_url'];
		}
		//微信支付下必填项
		if($this->config['utype'] == 'wxpay_branch_mp' || $this->config['utype'] == 'wxpay_app'){
			$data['openId'] = $this->params['wx_openid'];
			$data['terminalIp'] = $this->params['ip'];
			if($this->config['utype'] == 'wxpay_branch_mp'){
				$data['subAppid'] = $this->config['wx_appid'];
			}
		}
		//$data['tradeInformation'] = '{"business_type":5,"other_business_type":"order"}';
		return $data;
	}

	public function price($value='')
	{
		if($value && $value>0){
			if(strpos($value,'.') !== false){
				$value = intval($value*100);
			}
			$this->params['price'] = $value;
		}
		return $this->params['price'];
	}

	public function query()
	{
		global $app;
		$data = array();
		$data['requestNo'] = $this->params['sn'];
		$data['version'] = '1.0';
		$data['accessCode'] = $this->config['access_code'];
		$data['transType'] = 'TRANS_QUERY';
		$data['signType'] = 'RSA2';
		$data['mchNo'] = $this->config['mch_no'];
		$data['oriOutTransNo'] = $this->params['sn'];
		$data['signature'] = $this->signature($data);
		$app->lib('curl')->is_post(true);
		foreach($data as $key=>$value){
			$app->lib('curl')->post_data($key,$value);
		}
		return $app->lib('curl')->get_json($this->server_url);
	}

	public function server_url($url='')
	{
		if($url){
			$this->server_url = $url;
		}
		return $this->server_url;
	}

	public function set_env($act='')
	{
		if($act && $act == 'demo'){
			$this->server_url = 'http://test.allinpayhk.com/gateway/pay/consumeTrans';
			$this->bill_url = 'http://test.allinpayhk.com/gateway/download/downloadbill';
		}
		if($act && $act == 'product'){
			$this->server_url = 'https://oats.allinpay.com/gateway/pay/consumeTrans';
			$this->bill_url = 'https://oats.allinpay.com/gateway/download/downloadbill';
		}
		return true;
	}
	
	public function sha256check($data,$sign='')
	{
		ksort($data);
		$tmplist = array();
		foreach($data as $key=>$value){
			if($value != ''){
				$tmplist[] = $key.'='.$value;
			}
		}
		$content = implode("&",$tmplist);
		$public_key = file_get_contents($this->config['public_key']);
		$public_key = $this->key_format($public_key,true);
		$key = openssl_pkey_get_public($public_key);
		$ok = openssl_verify($content,base64_decode($sign), $key, 'SHA256');
		openssl_free_key($openssl_public_key);
		return $ok;
	}

	public function signature($data)
	{
		ksort($data);
		$tmplist = array();
		foreach($data as $key=>$value){
			if($value != ''){
				$tmplist[] = $key.'='.$value;
			}
		}
		$content = implode("&",$tmplist);
		$private_key = file_get_contents($this->config['private_key']);
		$private_key = $this->key_format($private_key);
		$key = openssl_pkey_get_private($private_key);
		openssl_sign($content, $signature, $key, "SHA256");
		openssl_free_key($key);
		return base64_encode($signature);
	}

	private function key_format($key,$ispublic=false)
	{
		if(strpos($key,'-----') !== false){
			return $key;
		}
		if($ispublic){
			$tmp = "-----BEGIN PUBLIC KEY-----\n";
		}else{
			$tmp = "-----BEGIN PRIVATE KEY-----\n";
		}
		$tmp .= chunk_split($key,64,"\n");
		if($ispublic){
			$tmp .= "\n-----END PUBLIC KEY-----";
		}else{
			$tmp .= "\n-----END PRIVATE KEY-----";
		}
		return $tmp;
	}
}