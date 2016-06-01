<?php
/*****************************************************************************************
	文件： session.php
	备注： SESSION基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2016年01月12日 06时28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session
{
	protected $timeout = 600;
	protected $config;
	protected $sid = 'PHPSESSION';
	protected $sessid = '';
	public function __construct($config='')
	{
		if($config && is_array($config)){
			$this->config($config);
		}
		session_name($this->sid);
		$session_id = isset($_POST[$this->sid]) ? $_POST[$this->sid] : (isset($_GET[$this->sid]) ? $_GET[$this->sid] : "");
		if($session_id && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$session_id)){
			session_id($session_id);
			$this->sessid($session_id);
		}else{
			$this->sessid();
		}
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('public');
	}

	public function start()
	{
		session_start();
		return true;
	}

	public function save_path($path='')
	{
		if($path){
			session_save_path($path);
		}
	}

	public function config($config)
	{
		if($config){
			$this->config = $config;
			if($config['timeout']){
				$this->timeout($config['timeout']);
			}
			if($config['id']){
				$this->sid($config['sid']);
			}
		}
		return $this->config;
	}

	public function sessid($sessid="")
	{
		if($sessid){
			$this->sessid = $sessid;
		}
		if(!$this->sessid){
			$this->sessid = session_id();
		}
		return $this->sessid;
	}

	public function sid($sid='')
	{
		if($sid){
			$this->sid = $sid;
		}
		return $this->sid;
	}

	public function timeout($timeout='')
	{
		if($timeout){
			$this->timeout = $timeout;
		}
		return $this->timeout;
	}
}
?>