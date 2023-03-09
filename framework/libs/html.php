<?php
/**
 * 远程访问类，适用于生成静态页及远程获取数据，系统生成静态页类也是基于此项生成
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月18日
**/
class html_lib
{
	private $app;
	private $purl;
	private $timeout = 30;
	private $socket;
	private $use_func = "fsockopen";
	private $is_gzip = false;
	private $is_proxy = false;
	private $proxy_service = "";
	private $proxy_user = "";
	private $proxy_pass = "";
	private $header_array;
	private $is_post = false;
	private $ip;
	private $postdata;
	private $include_header = false;

	public function __construct()
	{
		$fsock_exists = function_exists('fsockopen') && function_exists("socket_accept");
		$curl_exists = function_exists('curl_init');
		if(!$fsock_exists && !$curl_exists){
			$this->use_func = "error";
			$this->use_curl = 0;
		}else{
			$this->use_func = $curl_exists ? "curl" : "fsockopen";
		}
	}

	/**
	 * 设置私有参数
	 * @参数 $var 变量名
	 * @参数 $val 变量值
	**/
	public function setting($var,$val="")
	{
		$this->$var = $val;
	}

	/**
	 * 设置HTTP响应头部参数
	 * @参数 $key 变量名
	 * @参数 $value 变量值
	**/
	public function set_header($key,$value)
	{
		$this->header_array[$key] = $value;
	}

	/**
	 * 设置是否使用post，为false时使用GET模式
	 * @参数 $post 布尔值 true/false
	**/
	public function set_post($post=true)
	{
		$this->is_post = $post;
	}

	/**
	 * 设置要POST提交的数据
	 * @参数 $key 字段名
	 * @参数 $value 字段值，如果值为空，则表示注销要提交的这个字段，0不是空值
	**/
	public function set_postdata($key,$value='')
	{
		if(!$key){
			return false;
		}
		if($value == '' && $this->postdata[$key]){
			unset($this->postdata[$key]);
		}
		if($value){
			$this->postdata[$key] = $value;
		}
		return true;
	}

	/**
	 * 注销要提交的POST字段内容
	 * @参数 $key 字段名称
	 * @返回 true
	**/
	public function unset_postdata($key)
	{
		if($key && $this->postdata[$key]){
			unset($this->postdata[$key]);
		}
		return true;
	}

	/**
	 * 内部处理POST数据
	 * @参数 $data 要处理的post数据
	**/
	private function _set_postdata($data)
	{
		if(!$data){
			return false;
		}
		if(is_string($data)){
			parse_str($data,$list);
			$data = $list;
		}
		foreach($data as $key=>$value){
			$this->set_postdata($key,$value);
		}
		return true;
	}

	/**
	 * 设置IP，当主机无法获取gethostbyname
	 * @参数 $ip IP地址
	 * @返回 当前的IP或是您指定的IP
	**/
	public function ip($ip='')
	{
		if($ip){
			$this->ip = $ip;
		}
		return $this->ip;
	}

	/**
	 * 返回的结果集中是否包含Header信息
	 * @参数 $type 布尔值true/false
	**/
	public function include_header($type=false)
	{
		$this->include_header = $type ? true : false;
		return $this->include_header;
	}

	/**
	 * 设置超时时间，单位是秒，建议设置为5秒或是10秒
	 * @参数 $time 时间，单位是秒
	**/
	public function timeout($time=5)
	{
		if($time != ''){
			$this->timeout = $time;
		}
		return $this->timeout;
	}

	/**
	 * 取得远程数据
	 * @参数 $url 远程地址
	 * @参数 $post 要发送的POST数据
	 * @返回  远程字符串数据
	**/
	public function get_content($url,$post="")
	{
		if(!$url || $this->use_func == "error"){
			return false;
		}
		$url = str_replace("&amp;","&",$url);
		if($post){
			$this->_set_postdata($post);
		}
		$this->format_url($url);
		if($this->ip){
			$tmp = $this->purl['protocol'].$this->purl["host"];
			$tmp = substr($url,strlen($tmp));
			$url = $this->purl['protocol'].$this->ip.$tmp;
		}
		return $this->use_func == "curl" ? $this->_curl($url) : $this->_fsockopen($url);
	}

	private function _curl($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER,true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if($this->is_post && $this->postdata){
			curl_setopt($curl,CURLOPT_POST,true);
			curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($this->postdata));
		}else{
			curl_setopt($curl, CURLOPT_HTTPGET,true);
		}
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$this->timeout);//等待时间，超时退出
		if($this->is_gzip){
			curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP压缩
		}
		if($this->is_proxy && $this->proxy_service){
			curl_setopt($curl,CURLOPT_PROXY,$this->proxy_service);
			if($this->proxy_user || $this->proxy_pass){
				curl_setopt($curl,CURLOPT_PROXYUSERPWD,base64_encode($this->proxy_user.":".$this->proxy_pass));
			}
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		if($this->purl["user"]){
			$auth = $this->purl["user"].":".$this->purl["pass"];
			curl_setopt($curl, CURLOPT_USERPWD, $auth);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
		$header = array();
		$header[] = "Host: ".$this->purl["host"].":".$this->purl['port'];
		$header[] = "Referer: ".$this->purl['protocol'].$this->purl["host"];
		if($this->header_array && is_array($this->header_array)){
			foreach($this->header_array AS $key=>$value){
				$header[] = $key.": ".$value;
			}
		}
		if($post){
			$length = strlen($post);
			$header[] = 'Content-length: '.$length;
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

		$content = curl_exec($curl);
		if (curl_errno($curl) != 0){
			return false;
		}
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($http_header, $http_body) = preg_split($separator, $content, 2);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if($http_code != '200'){
			return false;
		}
		if($http_code == 301 || $http_code == 302){
			$matches = array();
			preg_match('/Location:(.*?)\n/', $http_header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url){
				return $data;
			}
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path']
			. (isset($url['query']) ? '?' . $url['query'] : '');
			$new_url = stripslashes($new_url);
			return $this->_curl($new_url);
		}
		return $http_body;
	}

	private function _fsockopen($url)
	{
		$crlf = $this->get_crlf();
		if($this->is_proxy && $this->proxy_service){
			$my_proxy = parse_url($this->proxy_service);
			if(!$my_proxy["port"]){
				$my_proxy["port"] = "80";
			}
			$handle = fsockopen($my_proxy["host"], $my_proxy['port'], $errno, $errstr, $this->timeout);
			if($this->is_post && $this->postdata){
				$tmp = explode("?",$url);
				$post = http_build_query($this->postdata);
				$new_url = count($tmp)>1 ? $url."&".$post : $url."?".$post;
				$out = "POST ".$new_url." HTTP/1.1".$crlf;
			}else{
				$out = "GET ".$url." HTTP/1.1".$crlf;
			}
			if($this->proxy_user || $this->proxy_pass){
				$out .= "Proxy-Authorization: Basic ".base64_encode ($this->proxy_user.":".$this->proxy_pass).$crlf.$crlf;
			}
		}else{
			$handle = fsockopen($this->purl["host"], $this->purl['port'], $errno, $errstr, $this->timeout);
			if($this->is_post && $this->postdata){
				$tmp = explode("?",$url);
				$post = http_build_query($this->postdata);
				$new_url = count($tmp)>1 ? $url."&".$post : $url."?".$post;
				$out = "POST ".$new_url." HTTP/1.1".$crlf;
			}else{
				$out = "GET ".$url." HTTP/1.1".$crlf;
			}
		}
		if(!$handle){
			return false;
		}
		set_time_limit($this->timeout);
		//取得内容信息
		$urlext = $this->purl["path"];
		if($urlext != "/" && $this->purl["query"]){
			$urlext .= "?";
			$urlext .= $this->purl["query"];
			if($this->purl["fragment"]){
				$urlext .= "#".$this->purl["fragment"];
			}
		}
		$out.= "Host: ".$this->purl["host"].$crlf;
		$out.= "Referer: ".$this->purl['protocol'].$this->purl["host"].$crlf;
		if($this->header_array && is_array($this->header_array)){
			foreach($this->header_array AS $key=>$value){
				$out .= $key.": ".$value.$crlf;
			}
		}
		$out.= "Connection: Close".$crlf.$crlf;
		if($this->is_gzip){
			$out .= "Accept-Encoding: GZIP".$crlf;
		}
		if(!fwrite($handle, $out)){
			return false;
		}
		$content = "";
		while(!feof($handle)){
			$content .= fgets($handle);
		}
 		fclose($handle);
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($http_header, $http_body) = preg_split($separator, $content, 2);
		if (strpos(strtolower($http_header), "transfer-encoding: chunked") !== false){
			$http_body = $this->unchunkHttp11($http_body);
		}
		if($this->is_gzip){
			return $this->gzip_decode($http_body);
		}
		return $http_body;
	}

	private function unchunkHttp11($data)
	{
		$fp = 0;
		$outData = "";
		while ($fp < strlen($data)){
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}

	private function get_crlf()
	{
		$crlf = '';
		if (strtoupper(substr(PHP_OS, 0, 3) === 'WIN')){
			$crlf = "\r\n";
		}elseif (strtoupper(substr(PHP_OS, 0, 3) === 'MAC')){
			$crlf = "\r";
		}else{
			$crlf = "\n";
		}
		return $crlf;
	}

	private function format_url($url)
	{
		$this->purl = parse_url($url);
		if(!isset($this->purl['host'])){
			if(isset($_SERVER["HTTP_HOST"])){
				$this->purl['host'] = $_SERVER["HTTP_HOST"];
			}elseif(isset($_SERVER["SERVER_NAME"])){
				$this->purl['host'] = $_SERVER["SERVER_NAME"];
			}else{
				$this->purl['host'] = "localhost";
			}
		}
		if(!$this->purl['scheme']){
			if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == "off" || $_SERVER["HTTPS"] == ""){
				$this->purl['scheme'] = "http";
			}else{
				$this->purl['scheme'] = "https";
			}
		}
		$this->purl['protocol'] = $this->purl['scheme'].'://';
		if(!$this->purl['port']){
			$this->purl['port'] = $_SERVER["SERVER_PORT"] ? $_SERVER["SERVER_PORT"] : 80;
		}
		if(!isset($this->purl['path'])){
			$this->purl['path'] = "/";
		}elseif(($this->purl['path']{0} != '/') && ($_SERVER["PHP_SELF"]{0} == '/')){
			$this->purl['path'] = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], '/') + 1) . $this->purl['path'];
		}
		return $this->purl;
	}

	private function gzip_decode($data)
	{
		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;
		if ($flags & 4) {
			$extralen = unpack('v' ,substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen += 2 + $extralen;
		}
		if ($flags & 8){
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}
		if ($flags & 16){
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}
		if ($flags & 2){
			$headerlen += 2;
		}
		$unpacked = @gzinflate(substr($data, $headerlen));
		if ($unpacked === false){
			$unpacked = $data;
		}
		return $unpacked;
	}
}