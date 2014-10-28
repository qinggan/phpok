<?php
/***********************************************************
	Filename: {phpok}libs/session.php
	Note	: SESSION通用版
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年8月17日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_lib
{
	var $sessid;
	var $timeout = 3600;
	function __construct()
	{
		//
	}

	function sessid($sid="")
	{
		if($sid) $this->sessid = $sid;
		return $this->sessid;
	}

	function config($config)
	{
		$this->config = $config;
		$this->timeout = $config["session_timeout"];
	}
}
?>