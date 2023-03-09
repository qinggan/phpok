<?php
/**
 * Redis缓存引挈
 * @package phpok\engine\cache\redis
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @update 2016年07月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cache_redis extends cache
{
	private $server = '127.0.0.1';
	private $port = '6379';
	private $conn;
	private $config_data;
	private $dbname = 0;
	private $dbpass = '';
	public function __construct($config)
	{
		$this->config_data = $config;
		parent::__construct($config);
		$this->config($config);
	}

	private function config($config)
	{
		$this->server = $config["server"] ? $config["server"] : "127.0.0.1";
		$this->port = $config["port"] ? $config["port"] : "6379";
		if($config['dbname']){
			$this->dbname = $config['dbname'];
		}
		if($config['dbpass']){
			$this->dbpass = $config['dbpass'];
		}
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

	/**
	 * 连接Redis
	**/
	private function start()
	{
		if(!class_exists('Redis')){
			$this->status(false);
			return false;
		}
		$this->conn = new Redis();
		$this->conn->connect($this->server,$this->port);
		if($this->conn->ping() == '+PONG'){
			if($this->dbpass){
				$this->conn->auth($this->dbpass);
			}
			$this->conn->select($this->dbname);
			return true;
		}
		$this->error('连接Redis服务器失败，请检查');
		$this->status(false);
		$this->__destruct();
		return false;
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
		$content = serialize($content);
		$this->conn->set($id,$content);
		//设置超时
		$this->conn->expire($id,$this->timeout);
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
		if(!$content){
			return false;
		}
		$content = unserialize($content);
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
		if($this->key_list && $this->key_list){
			unset($this->key_list[$id]);
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
		$this->conn->flushall();
		return true;
	}

	public function expired()
	{
		return true;
	}
}