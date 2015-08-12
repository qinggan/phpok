<?php
/***********************************************************
	Filename: {phpok}engine/session/default.php
	Note	: SESSION默认引挈
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月4日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_default
{
	var $sessid;
	var $timeout = 3600;
	var $config;
	function __construct($config)
	{
		$this->config($config);
		$sid = $config["id"] ? $config["id"] : "PHPSESSION";
		$this->sid = $sid;
		$session_id = isset($_POST[$sid]) ? $_POST[$sid] : (isset($_GET[$sid]) ? $_GET[$sid] : "");
		if($session_id && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$session_id))
		{
			session_id($session_id);
			$this->sessid = $session_id;
		}
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('public');
		session_save_path($GLOBALS['app']->dir_root.'data/session/');
		session_start();
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

	function config($config)
	{
		$this->config = $config;
		$this->timeout = $config["timeout"] ? $config["timeout"] : 600;
	}
}
?>