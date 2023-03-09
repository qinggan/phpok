<?php
/**
 * 腾迅STS接口
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年2月28日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class tencentcos_lib extends _init_lib
{
	private $url = 'https://sts.tencentcloudapi.com/';
	private $domain = 'sts.tencentcloudapi.com';
	private $secret_id = '';
	private $secret_key = '';
	private $bucket = '';
	private $region = '';
	private $timeout = 1800;
	private $client;

	public function __construct()
	{
		parent::__construct();
		//对于 PHP 版本 >=5.6 且 <7.2.5 的用户请下载cos-sdk-v5.phar以使用 Guzzle6 版本。
		if(version_compare(PHP_VERSION,'7.2.5','<')){
			include_once("phar://".dirname(__FILE__).'/cos-sdk-v5.phar');
		}else{
			//对于 PHP 版本 >=7.2.5 的用户请下载 cos-sdk-v7.phar 以使用 Guzzle7 版本
			include_once("phar://".dirname(__FILE__).'/cos-sdk-v7.phar');
		}
	}

	private function _hex2bin($data) {
		$len = strlen($data);
		return pack("H" . $len, $data);
	}
	/**
	 * obj 转 query string
	**/
	private function json2str($obj, $notEncode = false) {
		ksort($obj);
		$arr = array();
		if(!is_array($obj)){
			throw new Exception($obj + " must be a array");
		}
		foreach ($obj as $key => $val) {
			array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
		}
		return join('&', $arr);
	}

	/**
	 * v2接口的key首字母小写，v3改成大写，此处做了向下兼容
	**/
	private function backwardCompat($result)
	{
		if(!is_array($result)){
			throw new Exception($result + " must be a array");
		}
		$compat = array();
		foreach ($result as $key => $value) {
			if(is_array($value)) {
				$compat[lcfirst($key)] = $this->backwardCompat($value);
			} elseif ($key == 'Token') {
				$compat['sessionToken'] = $value;
			} else {
				$compat[lcfirst($key)] = $value;
			}
		}
		return $compat;
	}

	/**
	 * 计算临时密钥用的签名
	**/
	public function getSignature($opt, $key, $method)
	{
		$formatString = $method . $this->domain . '/?' . $this->json2str($opt, 1);
		$sign = hash_hmac('sha1', $formatString, $key);
		$sign = base64_encode($this->_hex2bin($sign));
		return $sign;
	}
	
	/**
	 * 获取临时密钥
	**/
	public function getTempKeys($config=array()) {
		if(!$config || !is_array($config) || count($config)<1){
			$config = $this->config();
		}
		if(array_key_exists('bucket', $config)){
			$ShortBucketName = substr($config['bucket'],0, strripos($config['bucket'], '-'));
			$AppId = substr($config['bucket'], 1 + strripos($config['bucket'], '-'));
		}
		if(array_key_exists('policy', $config)){
			$policy = $config['policy'];
		}else{
			$policy = array(
				'version'=> '2.0',
				'statement'=> array(
					array(
						'action'=> $config['allowActions'],
						'effect'=> 'allow',
						'principal'=> array('qcs'=> array('*')),
						'resource'=> array(
							'qcs::cos:' . $config['region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $config['allowPrefix']
						)
					)
				)
			);
		}
		$policyStr = str_replace('\\/', '/', json_encode($policy));
		$Action = 'GetFederationToken';
		$Nonce = rand(10000, 20000);
		$Timestamp = time();
		$Method = 'POST';
		$params = array(
			'SecretId'=> $config['secretId'],
			'Timestamp'=> $Timestamp,
			'Nonce'=> $Nonce,
			'Action'=> $Action,
			'DurationSeconds'=> $config['durationSeconds'],
			'Version'=>'2018-08-13',
			'Name'=> 'cos',
			'Region'=> 'ap-guangzhou',
			'Policy'=> urlencode($policyStr)
		);
		$params['Signature'] = $this->getSignature($params, $config['secretKey'], $Method, $config);
		$url = $config['url'];
		$ch = curl_init($url);
		if(array_key_exists('proxy', $config)){
			$config['proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['proxy']);
		}
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->json2str($params));
		$result = curl_exec($ch);
		if(curl_errno($ch)) $result = curl_error($ch);
		curl_close($ch);
		$result = json_decode($result, 1);
		if (isset($result['Response'])) {
			$result = $result['Response'];
			$result['startTime'] = $result['ExpiredTime'] - $config['durationSeconds'];
		}
		$result = $this->backwardCompat($result);
		return $result;
	}

	/**
	 * 获取策略信息
	**/
	public function getPolicy($scopes){
		if (!is_array($scopes)){
			return null;
		}
		$statements = array();

		for($i=0, $counts=count($scopes); $i < $counts; $i++){
			$actions=array();
			$resources = array();
			array_push($actions, $scopes[$i]->get_action());
			array_push($resources, $scopes[$i]->get_resource());
			$principal = array(
				'qcs' => array('*')
			);
			$statement = array(
				'actions' => $actions,
				'effect' => 'allow',
				'principal' => $principal,
				'resource' => $resources
			);
			array_push($statements, $statement);
		}

		$policy = array(
			'version' => '2.0',
			'statement' => $statements
		);
		return $policy;
	}

	public function url($val='')
	{
		if($val){
			$this->url = $val;
		}
		return $this->url;
	}

	public function domain($val='')
	{
		if($val){
			$this->domain = $val;
		}
		return $this->domain;
	}

	public function secret_id($val='')
	{
		if($val){
			$this->secret_id = $val;
		}
		return $this->secret_id;
	}

	public function secret_key($val='')
	{
		if($val){
			$this->secret_key = $val;
		}
		return $this->secret_key;
	}

	public function bucket($val='')
	{
		if($val){
			$this->bucket = $val;
		}
		return $this->bucket;
	}

	public function region($val='')
	{
		if($val){
			$this->region = $val;
		}
		return $this->region;
	}

	public function timeout($val='')
	{
		if($val){
			$this->timeout = $val;
		}
		return $this->timeout;
	}

	public function config()
	{
		$default = array(
			'url' => $this->url,
			'domain' => $this->domain,
			'secretId' => $this->secret_id, // 固定密钥
			'secretKey' => $this->secret_key, // 固定密钥
			'bucket' => $this->bucket, // 换成你的 bucket
			'region' => $this->region, // 换成 bucket 所在园区
			'durationSeconds' => $this->timeout, // 密钥有效期
			'allowPrefix' => '*',
			'allowActions' => array (
                "name/cos:PutObject",
                "name/cos:PostObject",
                "name/cos:InitiateMultipartUpload",
                "name/cos:ListMultipartUploads",
                "name/cos:ListParts",
                "name/cos:UploadPart",
                "name/cos:CompleteMultipartUpload",
                "name/cos:AbortMultipartUpload"
			)
		);
		return $default;
	}

	public function client()
	{
		$config = array();
		$config['region'] = $this->region;
		//$config['schema'] = 'https'; //协议头部，默认为http
		$config['credentials'] = array('secretId'=>$this->secret_id,'secretKey'=>$this->secret_key);
		try{
			$this->client = new Qcloud\Cos\Client($config);
		} catch (\Exception $e) {
			return $this->error($e->getMessage());
		}
		return $this->success();
	}

	/**
	 * 存在返回1，不存在返回空
	**/
	public function chk($file)
	{
		try {
			$result = $this->client->doesObjectExist($this->bucket,$file);
		} catch (\Exception $e) {
			return $this->error($e->getMessage());
		}
		return $result;
	}

	/**
	 * 复制文件
	**/
	public function copyfile($obj,$newobj)
	{
		try {
			$fromurl = $this->bucket.'.cos.'.$this->region.'.myqcloud.com/'.$obj;
			$result = $this->client->copyObject(array(
				"Bucket"=>$this->bucket,
				"Key"=>$newobj,
				"CopySource"=>$fromurl
			));
		} catch (\Exception $e) {
			return $this->error($e->getMessage());
		}
		return $this->success();
	}

	public function ico($obj)
	{
		return $obj.'?imageMogr2/thumbnail/!200x200r|imageMogr2/gravity/center/crop/200x200/interlace/0';
	}

	public function meta($obj)
	{
		try {
			$result = $this->client->headObject(array(
				'Bucket' => $this->bucket,
				'Key' => $obj
			));
		} catch (\Exception $e) {
			return $this->error($e->getMessage());
		}
		return $this->success($result);
	}

	public function del($obj)
	{
		try {
			$result = $this->client->deleteObject(array(
				'Bucket' => $this->bucket,
				'Key' => $obj
			));
		} catch (\Exception $e) {
			return $this->error($e->getMessage());
		}
		return $this->success($result);
	}

	/**
	 * 错误返回
	 * @参数 $error 错误内容
	 * @参数 $errid 错误ID
	 * @返回 数组
	**/
	private function error($error='',$errid=0)
	{
		if(!$error){
			$error = '异常';
		}
		$array = array('status'=>false,'error'=>$error);
		if($errid){
			$array['errid'] = $errid;
		}
		return $array;
	}

	/**
	 * 成功时返回的结果
	 * @参数 $info 返回的内容，支持字串，数组，及空
	**/
	private function success($info='')
	{
		$array = array('status'=>true);
		if($info != ''){
			$array['info'] = $info;
		}
		return $array;
	}
}