<?php
/**
 * 官网封装的curl
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年07月12日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class curl_lib
{
	private $is_gzip = true; // 是否启用 GZIP 压缩传输
	private $is_post = false; // 是否启用 POST，默认为 GET
	private $is_proxy = false; // 是否启用代理
	private $is_ssl = false; // 是否验证 SSL
	private $proxy_service = ''; // 代理服务器
	private $proxy_port = ''; // 代理端口
	private $proxy_user = ''; // 代理账号
	private $proxy_pass = ''; // 代理密码
	private $proxy_type = 'http'; // 代理方式
	private $post_data = array(); // POST 表单数据，数组
	private $http_body = ''; // 返回的内容信息
	private $http_header = ''; // 返回的头部信息
	private $http_code = 200; // 返回的状态
	private $host_ip = 0;
	private $headers = array(); // 发送的头部信息
	private $timeout = 30; // 超时时间，单位秒
	private $connect_timeout = 10; // 连接超时时间，单位秒
	private $ssl_ca_info = ''; // SSL 的 CA 证书
	private $ssl_cert_pem = ''; // SSL 的 PEM 证书
	private $ssl_cert_type = 'PEM';
	private $ssl_pass = ''; // SSL 的证书密码
	private $ssl_key = ''; // SSL 私钥
	private $ssl_key_pass = ''; // SSL 私钥密码
	private $ssl_key_type = 'PEM'; // SSL 私钥类型
	private $user = ''; //网址对应的用户
	private $pass = ''; //访问网址对应的密码
	private $referer = ''; // 自定义来源
	private $user_agent = ''; // 自定义
	private $cookie = array(); // COOKIE 信息，数组
	private $cookie_file = '';
	private $error = false;

	/**
	 * 超时设置
	**/
	public function timeout($val='')
	{
		if($val && is_numeric($val)){
			$this->timeout = $val;
		}
		return $this->timeout;
	}

	public function set_cookie($key,$value='')
	{
		$this->cookie[$key] = $value;
	}

	public function cookie($key='',$value='')
	{
		if(!$key){
			return $this->cookie;
		}
		if($value == ''){
			if(isset($this->cookie[$key])){
				return $this->cookie[$key];
			}
			return false;
		}
		$this->cookie[$key] = $value;
		return $this->cookie[$key];
	}

	/**
	 * Cookie 保存到一个文件
	 * @参数 $file 为字符串且目录可写时，为指定文件，为布尔值时表示删除cookie_file
	**/
	public function cookie_file($file='')
	{
		if($file && is_writable(dirname($file)) && !is_bool($file) && !is_numeric($file)){
			$this->cookie_file = $file;
		}
		if(is_bool($file) || is_numeric($file)){
			$this->cookie_file = '';
		}
		return $this->cookie_file;
	}

	/**
	 * 清除 Cookie File 文件
	**/
	public function cookie_file_clear()
	{
		$this->cookie_file = '';
	}

	/**
	 * 连接响应超时设置
	**/
	public function connect_timeout($val='')
	{
		if($val && is_numeric($val)){
			$this->connect_timeout = $val;
		}
		return $this->connect_timeout;
	}

	/**
	 * 设置是否启用 GZIP 压缩
	 * @参数 $state 布尔值 true 或 false 或 1 或 0
	**/
	public function is_gzip($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_gzip = $state;
		}
		return $this->is_gzip;
	}

	/**
	 * 设置是否启用 POST 发送信息
	 * @参数 $state 布尔值 true 或 false 或 1 或 0
	**/
	public function is_post($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_post = $state;
		}
		return $this->is_post;
	}

	/**
	 * 设置是否启用 POST 发送信息
	 * @参数 $state 布尔值 true 或 false 或 1 或 0
	**/
	public function is_proxy($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_proxy = $state;
		}
		return $this->is_proxy;
	}


	/**
	 * 设置是否支持 is_ssl 验证
	 * @参数 $state 布尔值 true 或 false 或 1 或 0
	**/
	public function is_ssl($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_ssl = $state;
		}
		return $this->is_ssl;
	}

	/**
	 * CA 证书
	**/
	public function ssl_ca_info($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_ca_info = $val;
		}
		return $this->ssl_ca_info;
	}

	/**
	 * SSL 的 PEM 证书
	**/
	public function ssl_cert_pem($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_cert_pem = $val;
		}
		return $this->ssl_cert_pem;
	}

	/**
	 * SSL 的 PEM 证书类型
	**/
	public function ssl_cert_type($val='')
	{
		if($val && in_array(strtoupper($val,array('PEM','DER','ENG')))){
			$this->ssl_cert_type = $val;
		}
		return $this->ssl_cert_type;
	}

	/**
	 * 证书密码
	**/
	public function ssl_pass($val='')
	{
		if($val != ''){
			$this->ssl_pass = $val;
		}
		return $this->ssl_pass;
	}

	/**
	 * SSL 的 私钥信息
	**/
	public function ssl_key($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_key = $val;
		}
		return $this->ssl_key;
	}

	/**
	 * SSL 的 私钥密码
	**/
	public function ssl_key_pass($val='')
	{
		if($val){
			$this->ssl_key_pass = $val;
		}
		return $this->ssl_key_pass;
	}

	/**
	 * SSL 的 私钥类型
	**/
	public function ssl_key_type($val='')
	{
		if($val && in_array(strtoupper($val),array('PEM','DER','ENG'))){
			$this->ssl_key_type = $val;
		}
		return $this->ssl_key_type;
	}

	/**
	 * HTTP 验证用户
	**/
	public function user($val='')
	{
		if($val != ''){
			$this->user = $val;
		}
		return $this->user;
	}

	public function pass($val='')
	{
		if($val != ''){
			$this->pass = $val;
		}
		return $this->pass;
	}

	public function referer($url='')
	{
		if($url){
			$this->referer = $url;
		}
		return $this->referer;
	}

	public function set_referer($url='')
	{
		return $this->referer($url);
	}

	public function user_agent($val='')
	{
		if($val){
			$this->user_agent = $val;
		}
		return $this->user_agent;
	}

	public function post_data($id='',$value='')
	{
		if($id){
			if($value != ''){
				$this->post_data[$id] = $value;
			}else{
				$this->post_data = $id;
			}
		}
		return $this->post_data;
	}

	/**
	 * 设置代理参数
	 * @参数 $service 代理服务器
	 * @参数 $port 代理端口
	 * @参数 $user 代理用户
	 * @参数 $pass 代理密码
	 * @参数 $type 代理模式，默认为 http，支持 http，socks4 socks5 socks4a socks5_hostname
	**/
	public function set_proxy($service='',$port='',$user='',$pass='',$type='')
	{
		if($service){
			$this->proxy_service = $service;
		}
		if($port){
			$this->proxy_port = $port;
		}
		if($user){
			$this->proxy_user = $user;
		}
		if($pass){
			$this->proxy_pass = $pass;
		}
		$this->proxy_set_type($type);
		return true;
	}

	/**
	 * 取得或设置代理参数
	 * @参数 $id 仅支持 service，port，user，pass 四个参数
	 * @参数 $value 值
	**/
	public function proxy($id,$value='')
	{
		$tmp = array('service','port','user','pass','type');
		if(!in_array($id,$tmp)){
			return false;
		}
		$tmp_id = 'proxy_'.$id;
		if($value != '' && $id != 'type'){
			$this->$tmp_id = $value;
		}
		if($value != '' && $id == 'type'){
			return $this->proxy_set_type($value);
		}
		return $this->$tmp_id;
	}

	public function proxy_set_type($type='http')
	{
		if(!$type){
			$type = 'http';
		}
		$type = strtolower($type);
		$tmp = array('http'=> CURLPROXY_HTTP,'socks4'=>CURLPROXY_SOCKS4,'socks5'=>CURLPROXY_SOCKS5,'socks4a'=>CURLPROXY_SOCKS4A,'socks5_hostname'=>CURLPROXY_SOCKS5_HOSTNAME);
		if(!in_array($type,array_keys($tmp))){
			$type = 'http';
		}
		$this->proxy_type = $tmp[$type];
	}

	/**
	 * 设置HTTP响应头部参数
	 * @参数 $key 变量名
	 * @参数 $value 变量值
	**/
	public function set_header($key,$value)
	{
		$this->headers[$key] = $value;
	}

	/**
	 * set_header 的别名
	**/
	public function set_head($key,$value)
	{
		return $this->set_header($key,$value);
	}

	public function http_code()
	{
		return $this->http_code;
	}

	/**
	 * 设置IP，当主机无法获取gethostbyname
	 * @参数 $ip IP地址
	 * @返回 当前的IP或是您指定的IP
	**/
	public function host_ip($ip='')
	{
		if($ip){
			$this->host_ip = $ip;
		}
		return $this->host_ip;
	}

	public function exec($url='')
	{
		if(!$url){
			return false;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER,true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if($this->is_post && $this->post_data){
			curl_setopt($curl,CURLOPT_POST,true);
			if(is_array($this->post_data)){
				$post = http_build_query($this->post_data);
				curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
				$this->set_header('Content-length',strlen($post));
			}else{
				curl_setopt($curl,CURLOPT_POSTFIELDS,$this->post_data);
				$this->set_header('Content-length',strlen($this->post_data));
			}
		}else{
			curl_setopt($curl, CURLOPT_HTTPGET,true);
		}
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$this->connect_timeout);//等待时间，超时退出
		if($this->is_gzip){
			curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP压缩
		}
		if($this->referer){
			curl_setopt($curl, CURLOPT_REFERER,$this->referer);
		}
		if($this->cookie && is_array($this->cookie)){
			curl_setopt($curl,CURLOPT_COOKIE,implode("; ",$this->cookie));
		}
		if($this->cookie_file){
			if(file_exists($this->cookie_file)){
				curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file); 
			}
			curl_setopt($curl, CURLOPT_COOKIEJAR,$this->cookie_file); 
		}
		if($this->is_proxy && $this->proxy_service){
			curl_setopt($curl,CURLOPT_HTTPPROXYTUNNEL,true);
			curl_setopt($curl,CURLOPT_PROXY,$this->proxy_service);
			curl_setopt($curl,CURLOPT_PROXYPORT,$this->proxy_port);
			if($this->proxy_user || $this->proxy_pass){
				curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
				curl_setopt($curl,CURLOPT_PROXYUSERPWD,base64_encode($this->proxy_user.":".$this->proxy_pass));
			}
			curl_setopt($curl, CURLOPT_PROXYTYPE, $this->proxy_type);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		if($this->user && $this->pass){
			curl_setopt($curl, CURLOPT_USERPWD, $this->user.":".$this->pass);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		//绑定IP后执行
		if($this->host_ip){
			$info = parse_url($url);
			$string = $info['scheme'].'://'.$info['host'];
			$url = $info['scheme'].'://'.$this->host_ip.substr($url,strlen($string));
			$port = $info['port'] ? $info['port'] : ($info['scheme'] == 'https' ? '443' : '80');
			$this->set_header('Host',$info['host'].':'.$port);
		}
		
		if($this->headers && is_array($this->headers)){
			$headers = array();
			foreach($this->headers as $key=>$value){
				$headers[] = $key.": ".$value;
			}
			if($headers && count($headers)>0){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		if($this->is_ssl){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
		}else{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if($this->ssl_ca_info){
			curl_setopt($curl, CURLOPT_CAINFO, $this->ssl_ca_info); 
		}
		if($this->ssl_cert_pem){
			curl_setopt($curl,CURLOPT_SSLCERTTYPE,$this->ssl_cert_type);
			curl_setopt($curl, CURLOPT_SSLCERT, $this->ssl_cert_pem); 
			if($this->ssl_pass){
				curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->ssl_pass); 
			}
		}
		if($this->ssl_key){
			curl_setopt($curl,CURLOPT_SSLKEYTYPE,$this->ssl_key_type);
			curl_setopt($curl,CURLOPT_SSLKEY,$this->ssl_key);
			if($this->ssl_key_pass){
				curl_setopt($curl,CURLOPT_SSLKEYPASSWD,$this->ssl_key_pass);
			}
		}
		$content = curl_exec($curl);
		if (curl_errno($curl) != 0){
			return false;
		}
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($this->http_header, $this->http_body) = preg_split($separator, $content, 2);
		if($this->http_body){
			$this->http_body = $this->_bom($this->http_body);
		}
		$this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if($this->http_code == 301 || $this->http_code == 302){
			$matches = array();
			preg_match('/Location:(.*?)\n/', $this->http_header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url){
				return true;
			}
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path']
			. (isset($url['query']) ? '?' . $url['query'] : '');
			$new_url = stripslashes($new_url);
			return $this->exec($new_url);
		}
		if($this->http_code != '200'){
			return false;
		}
		return true;
	}

	public function get_content($url='')
	{
		return $this->get_body($url);
	}

	public function get_body($url='')
	{
		if($url){
			$this->exec($url);
		}
		return $this->http_body;
	}

	public function get_header($url='')
	{
		if($url && is_string($url)){
			$this->exec($url);
		}
		if($url && is_bool($url)){
			$info = trim($this->http_header);
			$info = str_replace("\r","",$info);
			$list = explode("\n",$info);
			$rslist = array();
			foreach($list as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				$tmp = strstr($value,':');
				if($tmp && $tmp != $value){
					$tmpid = str_replace($tmp,'',$value);
					$rslist[trim($tmpid)] = trim(substr($tmp,1));
				}
			}
			return $rslist;
		}
		return $this->http_header;
	}

	public function get_json($url='')
	{
		if(!$this->is_post && !$this->post_data){
			$this->set_header('Content-Type','application/json; charset=utf-8');
		}
		if($url){
			$this->exec($url);
		}
		if($this->http_code != 200){
			$info = $this->error('错误，HTTP 返回代码'.$this->http_code,'json');
			return json_decode($info,true);
		}
		if(!$this->http_body){
			$info = $this->error('内容为空','json');
			return json_decode($info,true);
		}
		if(substr($this->http_body,0,1) != '{'){
			$info = $this->error('非 JSON 数据','json');
			return json_decode($info,true);
		}
		return json_decode($this->http_body,true);
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

	public function get_xml($url='')
	{
		if(!$this->is_post && !$this->post_data){
			$this->set_header('Content-Type','text/xml; charset=utf-8');
		}
		if($url){
			$this->exec($url);
		}
		if($this->http_code != 200){
			return $this->error('错误，HTTP 返回代码'.$this->http_code,'xml');
		}
		if(!$this->http_body){
			return $this->error('内容为空','xml');
		}
		return $this->http_body;
	}

	public function error($error='',$type='json')
	{
		$this->error = $error;
		if($type == 'xml'){
			$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
			$xml.= '<root><status>0</status><error><![CDATA['.$error.']]></error></root>';
			return $xml;
		}
		$array = array('status'=>false,'error'=>$error);
		return json_encode($array);
	}

	public function ok($type='json')
	{
		$this->error = false;
		if($type == 'xml'){
			$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
			$xml.= '<root><status>1</status></root>';
			return $xml;
		}
		$array = array('status'=>true);
		return json_encode($array);
	}
}
