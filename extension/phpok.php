<?php
/**
 * PHPOK 官方提供的 SDK 接口
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2020年10月9日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class phpok_lib
{
	private $_id = 0;
	private $_key = '';
	private $_server = '';
	private $_get = false;
	private $_ip = '';
	public function __construct()
	{
		//
	}

	public function app_id($appid = 0)
	{
		if($appid){
			$this->_id = $appid;
		}
		return $this->_id;
	}

	public function app_key($appkey='')
	{
		if($appkey != ''){
			$this->_key = $appkey;
		}
		return $this->_key;
	}

	public function ip($ip='')
	{
		if($ip){
			$this->_ip = $ip;
		}
		return $this->_ip;
	}
	
	public function server_url($url='')
	{
		if($url){
			$this->_server = $url;
		}
		return $this->_server;
	}

	public function sign($data='')
	{
		$string = '_appid='.$this->_id.'&_appkey='.$this->_key;
		if($data && is_array($data)){
			ksort($data);
			foreach($data as $key=>$value){
				if($value != ''){
					$string .= "&".$key."=".rawurlencode($value);
				}
			}
		}
		return md5($string);
	}

	public function content($data='')
	{
		$tmplist = array("_appid"=>$this->_id);
		$tmplist['_sign'] = $this->sign($data);
		if($data && is_array($data)){
			$tmplist['params'] = $data;
		}
		$info = $this->_curl($tmplist);
		if(!$info){
			return false;
		}
		$info = trim($info);
		if(substr($info,0,1) != '{'){
			return $info;
		}
		return json_decode($info,true);
	}

	private function _curl($data)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER,true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_POST,true);
		$post = http_build_query($data);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
		$headers = array();
		$headers[] = 'Content-length: '.strlen($post);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,15);//等待时间，超时退出
		curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP压缩
		curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		if($this->_ip){
			$info = parse_url($this->_server);
			$string = $info['scheme'].'://'.$info['host'];
			$url = $info['scheme'].'://'.$this->_ip.substr($url,strlen($string));
			$port = $info['port'] ? $info['port'] : ($info['scheme'] == 'https' ? '443' : '80');
			$headers[] = "Host: ".$info['host'].':'.$port;
		}else{
			$url = $this->_server;
		}
		$headers[] = 'Expect: ';
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		$content = curl_exec($curl);
		if (curl_errno($curl) != 0){
			return false;
		}
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($head, $body) = preg_split($separator, $content, 2);
		if($body){
			$body = $this->_bom($body);
		}
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if($code == 301 || $code == 302){
			$matches = array();
			preg_match('/Location:(.*?)\n/', $head, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url){
				return false;
			}
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path']
			. (isset($url['query']) ? '?' . $url['query'] : '');
			$new_url = stripslashes($new_url);
			$this->server_url($new_url);
			return $this->_curl($data);
		}
		if($code != '200'){
			return false;
		}
		return $body;
	}

	private function _bom($info)
	{
		$info = trim($info);
		$a1 = substr($info, 0, 1);
		$a2 = substr($info, 1, 1);
		$a3 = substr($info, 2, 1);
		if(ord($a1) == 239 && ord($a2) == 187 && ord($a3) == 191){
			return substr($info,3);
		}
		return $info;
	}
}
