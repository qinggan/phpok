<?php
/*****************************************************************************************
	文件： payment/unionpay.php
	备注： 银联支付
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月28日 10时28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class unionpay_lib
{
	private $param;
	private $form_params;
	private $order;
	private $cert_id;
	private $cert_file;
	private $cert_pwd = '0000';
	private $verify_file;
	public function __construct()
	{
		$this->form_params = array(
			'version' => '5.0.0',				//版本号
			'encoding' => 'utf-8',				//编码方式
			'txnType' => '01',				//交易类型	
			'txnSubType' => '01',				//交易子类
			'bizType' => '000201',				//业务类型
			'signMethod' => '01',		//签名方法
			'channelType' => '07',		//渠道类型，07-PC，08-手机
			'accessType' => '0',		//接入类型
			'currencyCode' => '156',	//交易币种
			'defaultPayType' => '0001'	//默认支付方式	
		);
	}

	public function form_param($id='',$val='')
	{
		$array = array('certId','frontUrl','backUrl','channelType','merId','orderId','txnTime','txnAmt','reqReserved','signature');
		if(!$id){
			return false;
		}
		if($val == ''){
			return false;
		}
		if(!in_array($id,$array)){
			return false;
		}
		$this->form_params[$id] = $val;
	}

	public function param($id,$val='')
	{
		$this->param[$id] = $val;
	}

	public function set_cert_id($file){
		if(!$file){
			return false;
		}
		$this->cert_file = $file;
		$this->form_params['certId'] = $this->get_cert_id($file);
	}

	public function set_verify_id($file)
	{
		if(!$file){
			return false;
		}
		$this->verify_file = $file;
	}

	public function set_channel_type($type='pc')
	{
		if(is_numeric($type) && $type){
			$this->form_params['channelType'] = '08';
		}else{
			if($type && $type == 'mobile'){
				$this->form_params['channelType'] = '08';
			}else{
				$this->form_params['channelType'] = '07';
			}
		}
	}

	public function txn_sub_type($type='default')
	{
		$this->form_params['txnSubType'] = $type == 'installment' ? '03' : '01';
	}

	private function get_cert_id($file)
	{
		$pkcs12certdata = file_get_contents($file);
		openssl_pkcs12_read ( $pkcs12certdata, $certs, $this->cert_pwd);
		$x509data = $certs ['cert'];
		openssl_x509_read($x509data);
		$certdata = openssl_x509_parse ( $x509data );
		$cert_id = $certdata ['serialNumber'];
		return $cert_id;
	}

	public function create_html()
	{
		$encodeType = isset ( $params ['encoding'] ) ? $params ['encoding'] : 'UTF-8';
		$html  = '<html>'."\n";
		$html .= '<head>'."\n";
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		$html .= '</head>'."\n";
		$html .= '<body  onload="javascript:document.pay_form.submit();">'."\n";
		$html .= '<form id="pay_form" name="pay_form" action="'.$this->param['form_url'].'" method="post">'."\n";
		foreach($this->form_params as $key=>$value){
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		}
		$html .= '<input type="submit" type="hidden" />'."\n".'</form>'."\n";
		$html .= '</body>'."\n".'</html>';
		return $html;
	}

	public function sign()
	{
		if(isset($this->form_params['transTempUrl'])){
			unset($this->form_params['transTempUrl']);
		}
		$params_str = $this->coverParamsToString($this->form_params);
		$params_sha1x16 = sha1($params_str,false);
		$private_key = $this->getPrivateKey($this->cert_file);
		$sign_falg = openssl_sign ( $params_sha1x16, $signature, $private_key, OPENSSL_ALGO_SHA1 );
		if ($sign_falg) {
			return base64_encode($signature);
		}
		return false;
	}

	public function sign_cert_pwd($val)
	{
		$this->cert_pwd = $val;
	}

	private function getPrivateKey($cert_path) {
		$pkcs12 = file_get_contents($cert_path);
		openssl_pkcs12_read($pkcs12,$certs,$this->cert_pwd);
		return $certs ['pkey'];
	}

	

	private function coverParamsToString($params)
	{
		$sign_str = '';
		ksort($params);
		foreach($params as $key => $val){
			if($key == 'signature'){
				continue;
			}
			$sign_str .= sprintf("%s=%s&",$key,$val);
		}
		return substr($sign_str,0,strlen($sign_str)-1);
	}

	public function verify($params) {
		$public_key = $this->getPulbicKeyByCertId($params['certId']);
		if(!$public_key){
			return false;
		}
		$signature_str = $params['signature'];
		unset($params['signature']);
		$params_str = $this->coverParamsToString($params);
		$signature = base64_decode($signature_str);
		$params_sha1x16 = sha1($params_str,false);
		return openssl_verify( $params_sha1x16, $signature,$public_key, OPENSSL_ALGO_SHA1 );
	}

	private function getPulbicKeyByCertId($certId) {
		if($this->getCertIdByCerPath() == $certId){
			return $this->getPublicKey();
		}
		return false;
	}

	private function getPublicKey()
	{
		return file_get_contents($this->verify_file);
	}

	private function getCertIdByCerPath() {
		$x509data = file_get_contents($this->verify_file);
		openssl_x509_read($x509data);
		$certdata = openssl_x509_parse($x509data);
		$cert_id = $certdata['serialNumber'];
		return $cert_id;
	}
}
?>