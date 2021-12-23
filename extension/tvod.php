<?php
/**
 * 腾迅VOD服务端API
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年5月26日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class tvod_lib
{
	private $_serverHost = "vod.api.qcloud.com";
	private $_serverPort = 80;
	private $_defaultRegion;
	private $_requestMethod = 'GET';
	private $_serverUri = "/v2/index.php";
	private $_secretId;
	private $_secretKey;
	private $_version = "SDK_PHP_1.4";

	public function __construct($config = '')
	{
		if($config && is_array($config) && $config['secretId'] && $config['secretKey'] && $config['region']){
			$this->config($secretId, $secretKey, $region);
		}
	}

	public function config($secretId, $secretKey, $region)
	{
		$this->_secretId = $secretId;
		$this->_secretKey = $secretKey;
		$this->_defaultRegion = $region;
	}

	public function action($paraMap)
	{
		$paraMap["Region"] = $this->_defaultRegion;
		$paraMap["SecretId"] = $this->_secretId;
		$paraMap["Timestamp"] = time();
		$paraMap["Nonce"] = rand(0, 1000000);
		$request = array();
		$this->makeRequest($paraMap["Action"], $paraMap, $request);
		if(!($response = $this->sendRequest($request, ""))){
			return $this->error($response);
		}
		if($response['info'] && $response['info']['code']){
			return $this->error($response['info']['message'],$response['info']['code']);
		}
		return $this->success($response['info']);
	}

	public function media_info($vid)
	{
		$paraMap = array(
			'Action' => "GetVideoInfo",							//接口名
			'fileId' => $vid,
			'infoFilter.0' => 'basicInfo'
		);
		return $this->action($paraMap);
	}

	public function media_delete($vid,$refresh=false)
	{
		$paraMap = array(
			'Action' => "DeleteVodFile",							//接口名
			'isFlushCdn'=> ($refresh ? 1 : 0),
			"priority" =>0,
			'fileId' => $vid
		);
		return $this->action($paraMap);
	}

	/**
 	* sendRequest
 	* @param array  $request    http请求参数
 	* @param string $data       发送的数据
 	* @return
 	*/
	private function sendRequest($request, $data)
	{  
		$url = $request['url'];
		$ch = curl_init($url);
		$MethodLine = "GET {$request['uri']}?{$request['query']} HTTP/1/1";
		$header = array(
			$MethodLine,
			"HOST:{$request['host']}",
			"Content-Length:".$request['contentLen'],
			"Content-type:application/octet-stream",
			"Accept:*/*",
			"User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36",
				
		);
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		
		// 证书
		// curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$response = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($response, true);
		if (!$result) {
			return $this->error($response);
		}
		return $this->success($result);
	}

	/**
	 * GetReqSign
	 * 生成请求的签名字符串
	 * @param array 	$paraMap  请求参数
	 * @return
	 */
	private function GetReqSign($paraMap) {
		$paramStr = "";
		ksort($paraMap);
		$i = 0;
		foreach ($paraMap as $key => $value) {
			if ($key == 'Signature'){
				continue;
			}
			// 把 参数中的 _ 替换成 .
			if (strpos($key, '_')){
				$key = str_replace('_', '.', $key);
			}
			if ($i == 0){
				$paramStr .= '?';
			}else{
				$paramStr .= '&';
			}
			$paramStr .= $key . '=' . $value;
			++$i;
		}
		$plainText = $this->_requestMethod . $this->_serverHost . $this->_serverUri . $paramStr;
		$cipherText = base64_encode(hash_hmac('sha1', $plainText, $this->_secretKey, true));
		return $cipherText;
	}

	private function makeRequest($name, $arguments, &$request)
	{
		$action = ucfirst($name);
		$params = $arguments;
		$params['Action'] = $action;
		$params['RequestClient'] = $this->_version;
		ksort($params);
		$params['Signature'] = $this->GetReqSign($params);
		$request['uri'] = $this->_serverUri;
		$request['host'] = $this->_serverHost;
		$request['query'] = http_build_query($params);
		$request['query'] = str_replace('+','%20',$request['query']);
		$url = $request['host'] . $request['uri'];
		if($this->_serverPort != '' && $this->_serverPort != 80 && $this->_serverPort != 443){
			$url = $request['host'] . ":" . $this->_serverPort . $request['uri'];
		}
		$url = $url.'?'.$request['query'];
		$url = 'https://'.$url;
		$request['url'] = $url;
		$request['contentLen'] = $arguments['contentLen'];
	}

	private function error($info='',$code='')
	{
		$data = array();
		$data['status'] = false;
		$data['info'] = $code ? $code.':'.$info : $info;
		return $data;
	}

	private function success($info='')
	{
		$data = array();
		$data['status'] = true;
		$data['info'] = $info;
		return $data;
	}
}
