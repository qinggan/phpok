<?php
/*****************************************************************************************
	文件： {phpok}/libs/token.php
	备注： 令牌生成器管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月9日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class token_lib
{
	private $keyid;
	private $keyc_length = 6;
	private $keya;
	private $keyb;
	private $time;
	private $expiry = 3600;
	
	public function __construct()
	{
		$this->_keyid();
		$this->config();
		$this->time = time();
	}

	public function __destruct()
	{
		unset($this);
	}

	public function keyid($keyid='')
	{
		if(!$keyid){
			return $this->keyid;
		}
		$this->keyid = strtolower(md5($keyid));
		$this->config();
		return $this->keyid;
	}

	private function config()
	{
		if(!$this->keyid){
			return false;
		}
		$this->keya = md5(substr($this->keyid, 0, 16));
		$this->keyb = md5(substr($this->keyid, 16, 16));
	}

	//创建一个KEY-ID
	private function _keyid()
	{
		global $app;
		if($app->site && $app->site['api_code']){
			$api_code = $app->site['api_code'];
			unset($app);
		}else{
			if($_SESSION['phpok-site-api-code']){
				$api_code = $_SESSION['phpok-site-api-code'];
			}else{
				$api_code = uniqid(time(), true);
				$_SESSION['phpok-site-api-code'] = $api_code;
			}
		}
		$this->keyid = strtolower(md5($api_code));
		return true;
	}

	//设置超时
	public function expiry($time=0)
	{
		$this->expiry = $time;
	}

	//加密数据
	public function encode($string)
	{
		if(!$this->keyid){
			return false;
		}
		$string = serialize($string);
		$string = sprintf('%010d',($this->expiry + $this->time)).substr(md5($string.$this->keyb), 0, 16).$string;
		$keyc = substr(md5(microtime().rand(1000,9999)), -$this->keyc_length);
		$cryptkey = $this->keya.md5($this->keya.$keyc);
		$rs = $this->core($string,$cryptkey);
		return $keyc.base64_encode($rs);
	}

	//解密
	public function decode($string)
	{
		if(!$this->keyid){
			return false;
		}
		$keyc = substr($string, 0, $this->keyc_length);
		$string = base64_decode(substr($string, $this->keyc_length));
		$cryptkey = $this->keya.md5($this->keya.$keyc);
		$rs = $this->core($string,$cryptkey);
		$chkb = substr(md5(substr($rs,26).$this->keyb),0,16);
		if((substr($rs, 0, 10) - $this->time > 0) && substr($rs, 10, 16) == $chkb)
		{
			$info = substr($rs, 26);
			return unserialize($info);
		}
		return false;
	}

	private function core($string,$cryptkey)
	{
		$key_length = strlen($cryptkey);
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		// 产生密匙簿
		for($i = 0; $i <= 255; $i++){
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
		for($j = $i = 0; $i < 256; $i++){
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		// 核心加解密部分
		for($a = $j = $i = 0; $i < $string_length; $i++){
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		return $result;
	}
}
?>