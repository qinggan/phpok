<?php
/**
 * 京东支付类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年5月20日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class jdpay_lib
{
	private $key_md5;
	private $key_3des;
	private $private_pem;
	private $public_pem;
	private $dir_root;
	private $dir_pay;
	private $config;
	
	public function __construct($config)
	{
		if($config && is_array($config)){
			$this->config($config);
		}
		if(!$config['dir_root']){
			$this->dir_root = defined( 'ROOT' ) ? ROOT : __DIR__.'/../../../';
		}
		$this->dir_pay = $this->dir_root.'gateway/payment/jdpay/';
	}

	public function config($config='',$val='')
	{
		if(!$config){
			return $this->config;
		}
		$data = array('key_md5','key_3des','prikey','pubkey');
		if(is_array($config)){
			foreach($config as $key=>$value){
				if($value != ''){
					$this->config[$key] = $value;
					if($key == 'key_md5'){
						$this->key_md5 = $value;
					}
					if($key == 'key_3des'){
						$this->key_3des = base64_decode($value);
					}
					if($key == 'prikey'){
						$this->private_pem = $this->private_pem($value);
					}
					if($key == 'pubkey'){
						$this->public_pem = $this->public_pem($value);
					}
				}
			}
		}else{
			if($val != '' && is_string($config)){
				$this->config[$config] = $val;
				if($config == 'key_md5'){
					$this->key_md5 = $value;
				}
				if($config == 'key_3des'){
					$this->key_3des = $value;
				}
				if($config == 'prikey'){
					$this->private_pem = $this->private_pem($value);
				}
				if($config == 'pubkey'){
					$this->public_pem = $this->public_pem($value);
				}
			}
		}
		return true;
	}

	public function decrypt($data)
	{
		$hexSourceData = array ();
		$hexSourceData = $this->hex2bytes($data);
		$unDesResult = $this->decrypt_3des($this->to_string($hexSourceData));
		$unDesResultByte = $this->get_bytes($unDesResult);
		$dataSizeByte = array ();
		for($i = 0; $i < 4; $i ++) {
			$dataSizeByte [$i] = $unDesResultByte [$i];
		}
		$dsb = $this->byte2int( $dataSizeByte, 0 );
		$tempData = array ();
 		for($j = 0; $j < $dsb; $j++) {
 			$tempData [$j] = $unDesResultByte [4 + $j];
 		}
		return $this->hex_to_bin($this->bytes2hex( $tempData ));
	}

	public function decrypt_rsa($data)
	{
		$pu_key = $this->public_pem();
		$decrypted = "";
		$data = base64_decode($data);
		openssl_public_decrypt($data,$decrypted,$pu_key);//公钥解密
		return $decrypted;
	}

	public function encrypt($sourceData)
	{
		$source = $this->get_bytes($sourceData);
		$merchantData = count($source);
		$x = ($merchantData + 4) % 8;
		$y = ($x == 0) ? 0 : (8 - $x);
		$sizeByte = $this->integerToBytes( $merchantData );
		$resultByte = array ();
		for($i = 0; $i < 4; $i ++) {
			$resultByte [$i] = $sizeByte [$i];
		}
		for($j = 0; $j < $merchantData; $j ++) {
			$resultByte [4 + $j] = $source [$j];
		}
		for($k = 0; $k < $y; $k ++) {
			$resultByte [$merchantData + 4 + $k] = 0x00;
		}
		$desdata = $this->encrypt_3des($this->to_string($resultByte));
		return $this->str2hex( $desdata );
	}
	

	public function encrypt_rsa($data)
	{
		$pi_key = $this->private_pem();
		$encrypted="";
		openssl_private_encrypt($data,$encrypted,$pi_key,OPENSSL_PKCS1_PADDING);//私钥加密
		$encrypted = base64_encode($encrypted);
		return $encrypted;
	}

	public function notify_data($resultData){
		$resultXml = simplexml_load_string($resultData);
		$resultObj = json_decode(json_encode($resultXml),true);
		$encryptStr = $resultObj["encrypt"];
		$encryptStr=base64_decode($encryptStr);
		$reqBody = $this->decrypt($encryptStr);
		$bodyXml = simplexml_load_string($reqBody);
		$resData = json_decode(json_encode($bodyXml),true);
		$inputSign = $resData["sign"];

		$startIndex = strpos($reqBody,"<sign>");
		$endIndex = strpos($reqBody,"</sign>");
		$xml;
		
		if($startIndex!=false && $endIndex!=false){
			$xmls = substr($reqBody, 0,$startIndex);
			$xmle = substr($reqBody,$endIndex+7,strlen($reqBody));
			$xml=$xmls.$xmle;
		}
		$sha256SourceSignString = hash("sha256", $xml);
		$decryptStr = $this->decrypt_rsa($inputSign);
		if($decryptStr==$sha256SourceSignString){
			$flag=true;
		}else{
			$flag=false;
		}
		$resData["version"]=$resultObj["version"];
		$resData["merchant"]=$resultObj["merchant"];
		$resData["result"]=$resultObj["result"];
		$resData['status'] = $flag;
		phpok_log($resData);
		return $resData;
	}


	public function private_pem($file='')
	{
		if($file){
			$tmp = is_file($file) ? file_get_contents($file) : $file;
			$this->private_pem = $this->pem_format($tmp);
		}
		return $this->private_pem;
	}

	public function public_pem($file='')
	{
		if($file){
			$tmp = is_file($file) ? file_get_contents($file) : $file;
			$this->public_pem = $this->pem_format($tmp,true);
		}
		return $this->public_pem;
	}

	public function sign($params,$unSignKeyList='') {
		ksort($params);
		$string = $this->array2string($params,$unSignKeyList);
		$input = hash ( "sha256", $string);
		return $this->encrypt_rsa($input);
	}

	public function submit($params,$ispc=true)
	{
		$data = array();
		$data['version'] = '2.0';
		$data['merchant'] = $this->config['merchant'];
		if($this->config['pay_merchant']){
			$data['payMerchant'] = $this->config['pay_merchant'];
		}
		if($this->config['device']){
			$data['device'] = $this->config['device'];
		}
		$data['tradeNum'] = $params['sn'];
		$data['tradeName'] = $params['title'];
		$data['tradeTime'] = date("YmdHis",$params['addtime']);
		$data['amount'] = intval($params['price']*100).'';
		$data['orderType'] = $params['is_virtual'] ? '1' : '0';
		if($params['industryCategoryCode']){
			$data['industryCategoryCode'] = $params['industryCategoryCode'];
		}
		$data['currency'] = 'CNY';
		if($params['note']){
			$data['note'] = $params['note'];
		}
		$data['callbackUrl'] = $params['return_url'];
		$data['notifyUrl'] = $params['notify_url'];
		if($params['ip']){
			$data['ip'] = $params['ip'];
		}
		$data['userId'] = $params['user_token'];
		if($params['goodsInfo']){
			$data['goodsInfo'] = $params['goodsInfo'];
		}
		if($params['receiverInfo']){
			$data['receiverInfo'] = $params['receiverInfo'];
		}
		if($params['riskInfo']){
			$data['riskInfo'] = $params['riskInfo'];
		}
		if($ispc){
			$form_url = "https://wepay.jd.com/jdpay/saveOrder";
		}else{
			$form_url = "https://h5pay.jd.com/jdpay/saveOrder";
		}
		$data['expireTime'] = ($this->config['expire_time'] && $this->config['expire_time'] >=600) ? $this->config['expire_time'] : '1800';
		$list = array();
		foreach($data as $key=>$value){
			if($key != 'merchant' && $key != 'version' && $key != 'sign'){
				$list[$key] = $this->encrypt($value);
			}else{
				$list[$key] = $value;
			}
		}
		$html = '<form action="'.$form_url.'" method="post" id="jdpay_submit">';
		foreach($list as $key=>$value){
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
		$sign = $this->sign($data);
		$html .= '<input type="hidden" name="sign" value="'.$sign.'"/>';
		$html .= '</form>';
		return $html;
	}


	public function verify($data,$sign='')
	{
		foreach($data as $key=>$value){
			if($key != 'merchant' && $key != 'version' && $key != 'sign' && $value != ''){
				$data[$key] = $this->decrypt($value);
			}
		}
		$strSourceData = $this->array2string($data);
		$decryptStr = $this->decrypt_rsa($sign);
		phpok_log($decryptStr);
		$sha256SourceSignString = hash ( "sha256", $strSourceData);
		phpok_log($sha256SourceSignString);
		if($decryptStr!=$sha256SourceSignString){
			return false;
		}
		return true;
	}

	private function array2string($data,$unSignKeyList='')
	{
		$linkStr="";
		$isFirst=true;
		ksort($data);
		if(!$unSignKeyList){
			$unSignKeyList = array();
		}
		if(is_string($unSignKeyList)){
			$unSignKeyList = explode(",",$unSignKeyList);
		}
		foreach($unSignKeyList as $key=>$value){
			if($value == ''){
				unset($unSignKeyList[$key]);
			}
		}
		foreach($data as $key=>$value){
			if($value==null || $value==""){
				continue;
			}
			$bool=false;
			foreach ($unSignKeyList as $str) {
				if($key."" == $str.""){
					$bool=true;
					break;
				}
			}
			if($bool){
				continue;
			}
			if(!$isFirst){
				$linkStr.="&";
			}
			$linkStr.=$key."=".$value;
			if($isFirst){
				$isFirst=false;
			}
		}
		return $linkStr;
	}

	/**
	 * 将byte数组 转换为int
	 */
	private function byte2int($b, $offset) {
		$value = 0;
		for($i = 0; $i < 4; $i ++) {
			$shift = (4 - 1 - $i) * 8;
			$value = $value + ($b [$i + $offset] & 0x000000FF) << $shift; // 往高位游
		}
		return $value;
	}

	/**
	 * 字符串转16进制
	**/
	private function bytes2hex($bytes) {
		$str = $this->to_string( $bytes );
		return $this->str2hex( $str );
	}

	private function decrypt_3des($encrypted) {
		$td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' ); // 使用MCRYPT_DES算法,cbc模式
		$iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
		$ks = mcrypt_enc_get_key_size ( $td );
		@mcrypt_generic_init ( $td, $this->key_3des, $iv ); // 初始处理
		$decrypted = mdecrypt_generic ( $td, $encrypted ); // 解密
		mcrypt_generic_deinit ( $td ); // 结束
		mcrypt_module_close ( $td );
		return $decrypted;
	}

	private function encrypt_3des($input){
		$size = mcrypt_get_block_size ( 'des', 'ecb' );
		$td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
		$iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
		@mcrypt_generic_init ( $td, $this->key_3des, $iv );
		$data = mcrypt_generic ( $td, $input );
		mcrypt_generic_deinit ( $td );
		mcrypt_module_close ( $td );
		return $data;
	}

	private function get_bytes($string){
		$bytes = array ();
		for($i = 0; $i < strlen ( $string ); $i ++) {
			$bytes [] = ord ( $string [$i] );
		}
		return $bytes;
	}

	/**
	 * 转换一个16进制hexString字符串为十进制byte数组
	 * @param $hexString 需要转换的十六进制字符串        	
	 * @return 一个byte数组
	 */
	private function hex2bytes($hexString) {
		$bytes = array ();
		for($i = 0; $i < strlen ( $hexString ) - 1; $i += 2) {
			$bytes [$i / 2] = hexdec ( $hexString [$i] . $hexString [$i + 1] ) & 0xff;
		}
		return $bytes;
	}

	private function hex_to_bin($hexstr)
	{
		$n = strlen($hexstr);
		$sbin="";
		$i=0;
		while($i<$n){
			$a =substr($hexstr,$i,2);
			$c = pack("H*",$a);
			if ($i==0){$sbin=$c;}
			else {$sbin.=$c;}
			$i+=2;
		}
		return $sbin;
	}

	private function integerToBytes($val) {
		$byt = array ();
		$byt [0] = ($val >> 24 & 0xff);
		$byt [1] = ($val >> 16 & 0xff);
		$byt [2] = ($val >> 8 & 0xff);
		$byt [3] = ($val & 0xff);
		return $byt;
	}

	private function pad2Length($text, $padlen)
	{
		$len = strlen ( $text ) % $padlen;
		$res = $text;
		$span = $padlen - $len;
		for($i = 0; $i < $span; $i ++) {
			$res .= chr ( $span );
		}
		return $res;
	}

	private function pem_format($key,$ispublic=false)
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
	
	private function pkcs5_pad($text, $blocksize)
	{
		$pad = $blocksize - (strlen ( $text ) % $blocksize);
		return $text . str_repeat ( chr ( $pad ), $pad );
	}
	
	private function pkcs5_unpad($text)
	{
		$pad = ord ( $text {strlen ( $text ) - 1} );
		if ($pad > strlen ( $text ))
			return false;
		if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
			return false;
		return substr ( $text, 0, - 1 * $pad );
	}

	private function str2hex($string) {
		$hex = "";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			$tmp = dechex ( ord ( $string [$i] ) );
			if (strlen ( $tmp ) == 1) {
				$hex .= "0";
			}
			$hex .= $tmp;
		}
		$hex = strtolower ( $hex );
		return $hex;
	}

	private function to_string($bytes)
	{
		$str = '';
		foreach ( $bytes as $ch ) {
			$str .= chr ( $ch );
		}
		return $str;
	}
}