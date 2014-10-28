<?php
/***********************************************************
	Filename: phpok/engine/session/file.php
	Note	: 自定义SESSION存储目录
	Version : 4.0
	Author  : qinggan
	Update  : 2011-11-07 15:54
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class session_file
{
	var $save_path = "./data/session/";
	var $sessid;
	var $sys_time;
	var $timeout = 3600;
	var $config;
	var $sid;

	function __construct($config)
	{
		if(!$config || !is_array($config))
		{
			$config["id"] = "PHPSESSID";
			$config["path"] = "./data/session/";
			$config["timeout"] = 3600;
		}
		$this->config($config);
		$sid = $config["id"] ? $config["id"] : "PHPSESSION";
		session_name($sid);
		$this->sid = $sid;
		$session_id = isset($_POST[$sid]) ? $_POST[$sid] : (isset($_GET[$sid]) ? $_GET[$sid] : "");
		if($session_id)
		{
			session_id($session_id);
			$this->sessid = $session_id;
		}
		else
		{
			$this->sessid = session_id();
		}
		session_save_path($config["path"]);
		$this->config = $config;
		$this->timeout = $config["timeout"] ? $config["timeout"] : 600;
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('public');
		session_start();
	}


	function config($config)
	{
		$this->config = $config;
		$this->timeout = $config["timeout"] ? $config["timeout"] : 600;
	}


	function sessid($sid="")
	{
		if($sid) $this->sessid = $sid;
		if(!$this->sessid) $this->sessid = session_id();
		return $this->sessid;
	}

	function sid()
	{
		return $this->sid;
	}
}
?>