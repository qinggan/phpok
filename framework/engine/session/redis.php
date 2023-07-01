<?php
/**
 * 基于 Redis 的SESSION
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2022年12月8日
**/
class session_redis extends session
{
	private $conn;
	private $host = '127.0.0.1';
	private $port = '6379';
	private $prefix = 'sess_';
	private $dbname = 0;
	private $dbpass ='';
	private $count=0;
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		if($this->config['prefix']){
			$this->prefix = $this->config['prefix'];
		}
		if(!class_exists('Redis')){
			$this->error('缺少 Redis 组件库');
			return false;
		}
		$this->_connect();
		session_set_save_handler(array($this,"open"),array($this,"close"),array($this,"read"),array($this,"write"),array($this,"destory"),array($this,"gc"));
		$this->start();
	}

	public function __destruct(){
		if($this->conn){
			$this->conn->close();
			unset($this->conn);
		}
		session_write_close();
		return true;
	}

	public function config($config)
	{
		parent::config($config);
		$this->dbname = $config['dbname'] ? $config['dbname'] : 0;
		if($config['dbpass']){
			$this->dbpass = $config['dbpass'];
		}
	}

	public function open($save_path,$session_name)
	{
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($sid="")
	{
		$this->_connect();
		$this->sessid($sid);
		return $this->conn->get($this->prefix.$this->sessid);
	}

	public function write($sid,$data)
	{
		$this->_connect();
		$this->sessid($sid);
		$timeout = $this->timeout;
		if($timeout<600){
			$timeout = 600;
		}
		$this->conn->setex($this->prefix.$this->sessid, $timeout, $data);
		return true;
	}

	public function destory($sid)
	{
		if($sid){
			$this->sessid($sid);
			$this->conn->delete($this->prefix.$this->sessid);
		}
		return true;
	}

	public function gc()
	{
		return true;
	}

	private function _connect()
	{
		if(!$this->conn){
			$this->conn = new Redis();
			if(!$this->conn){
				$this->error('连接数据库失败');
			}
			$this->conn->connect($this->config['host'],$this->config['port'],3);
			if($this->conn->ping() == '+PONG'){
				if($this->dbpass){
					$this->conn->auth($this->dbpass);
				}
				$this->conn->select($this->dbname);
			}
		}
	}
}