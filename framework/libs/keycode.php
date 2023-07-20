<?php
/**
 * 安全随机码生成并或验证功能
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2014年6月3日
 * @更新 2023年7月20日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class keycode_lib
{
	public $keyid;
	
	function __construct()
	{
		$keyid = $GLOBALS['app']->config['spam_key'];
		if(!$keyid)
		{
			$keyid = $this->_keyid_create();
		}
		$this->keyid = $keyid;
	}

	function code()
	{
		//
	}

	private function _keyid_create()
	{
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$port = $_SERVER["SERVER_PORT"];
		$myurl = $_SERVER["SERVER_NAME"];
		if($port != "80" && $port != "443")
		{
			$myurl .= ":".$port;
		}
		$docu = $_SERVER["PHP_SELF"];
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1)
		{
			foreach($array AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					if(($key+1) < $count)
					{
						$myurl .= "/".$value;
					}
				}
			}
		}
		$myurl .= "/";
		$myurl = str_replace("//","/",$myurl);
		return md5($http_type.$myurl);
	}
}