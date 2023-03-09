<?php
/***********************************************************
	Filename: {phpok}/engine/cache/memcache.php
	Note	: Memcache引挈
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cache_memcache extends cache
{
	private $server = 'localhost';
	private $port = '11211';
	private $conn;
	private $config_data;
	public function __construct($config)
	{
		$this->config_data = $config;
		parent::__construct($config);
		$this->config($config);
	}

	private function config($config)
	{
		$this->server = $config["server"] ? $config["server"] : "localhost";
		$this->port = $config["port"] ? $config["port"] : "11211";
		if($this->status && !$this->conn){
			$this->start();
		}
	}


	public function __destruct()
	{
		parent::__destruct();
		if($this->conn){
			$this->conn->close();
		}
	}
	
	private function start()
	{
		if(class_exists('Memcache')){
			$this->conn = new Memcache;
			$this->conn->connect($this->server,$this->port);
			$info = $this->conn->getExtendedStats();
			$str = $this->server.':'.$this->port;
			if(!$info || !$info[$str]){
				$this->error("连接Memcache服务器失败，请检查");
				$this->status(false);
				$this->__destruct();
				return false;
			}
			return true;
		}else{
			$this->status(false);
			return false;
		}
	}

	//设置缓存状态
	public function status($status="")
	{
		if(is_bool($status) || is_numeric($status)){
			$this->status = $status ? true : false;
			if($this->status && !$this->conn){
				$this->start();
			}
		}
		return $this->status;
	}

	//写入缓存信息
	public function save($id,$content='')
	{
		if(!$this->status || $content === ''){
			return false;
		}
		if(!$this->conn){
			$this->config($this->config_data);
			if(!$this->conn){
				return false;
			}
		}
		$this->_time();
		$this->conn->set($id,$content,MEMCACHE_COMPRESSED,$this->timeout);
		$this->_time();
		$this->_count();
		return true;
	}

	public function get($id,$onlycheck=false)
	{
		if(!$id || !$this->status){
			return false;
		}
		if(!$this->conn){
			$this->config($this->config_data);
			if(!$this->conn){
				return false;
			}
		}
		$this->_time();
		$content = $this->conn->get($id);
		$this->_time();
		$this->_count();
		if($onlycheck){
			if($content == ''){
				return false;
			}
			return true;
		}
		if($content){
			return $content;
		}
		return false;
	}

	public function delete($id)
	{
		if($this->conn){
			$this->conn->delete($id);
		}		
		return true;
	}

	public function clear()
	{
		if(!$this->conn){
			$this->config($this->config_data);
			if(!$this->conn){
				return false;
			}
		}
		$this->conn->flush();
		return true;
	}

	public function expired()
	{
		return true;
	}
}