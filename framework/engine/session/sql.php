<?php
/***********************************************************
	Filename: phpok/engine/session/sql.php
	Note	: 将SESSION放在数据库中
	Version : 4.0
	Author  : qinggan
	Update  : 2016年01月12日 06时47分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_sql extends session
{
	private $table = 'qinggan_session';
	private $time;
	private $db;

	public function __construct($config)
	{
		if(!$config || !is_array($config)){
			$config["id"] = "PHPSESSID";
			$config["table"] = "session";
			$config["timeout"] = 600;
		}
		if(!$config['id']){
			$config["id"] = "PHPSESSID";
		}
		if(!$config['table']){
			$config['table'] = 'session';
		}
		if(!$config['timeout']){
			$config["timeout"] = 600;
		}
		parent::__construct($config);
		$this->config($config);
		$this->time = $app->time;
	}

	public function auto_start($db)
	{
		$var = 'db_'.$db['file'];
		$this->db = new $var($db);
		session_set_save_handler(array($this,"open"),array($this,"close"),array($this,"read"),array($this,"write"),array($this,"destory"),array($this,"gc"));
		$this->start();
	}

	public function config($config)
	{
		parent::config($config);
		$this->table = $config['table'];
	}

	public function open($save_path,$session_name)
	{
		if(substr($this->table,0,strlen($this->db->prefix)) != $this->db->prefix){
			$this->table = $this->db->prefix.''.$this->table;
		}
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($sid="")
	{
		$this->sessid($sid);
		$rs = $this->db->get_one("SELECT * FROM ".$this->table." WHERE id='".$this->sessid."'");
		if(!$rs){
			$sql = "INSERT INTO ".$this->table."(id,data,lasttime) VALUES('".$this->sessid."','','".$this->time."')";
			$this->db->query($sql);
			return false;
		}else{
			if(!$rs["data"]){
				return false;
			}
			return $rs["data"];
		}
	}

	public function write($sid,$data)
	{
		$this->sessid($sid);
		$this->db->query("UPDATE ".$this->table." SET data='".$data."',lasttime='".$this->time."' WHERE id='".$this->sessid."'");
		return true;
	}

	public function destory($sid)
	{
		$this->sessid($sid);
		$this->db->query("DELETE FROM ".$this->table." WHERE id='".$this->sessid."'");
		return true;
	}

	public function gc()
	{
		$this->db->query("DELETE FROM ".$this->table." WHERE lasttime+".$this->timeout."<'".$this->time."'");
		return true;
	}

	public function __destruct()
	{
		$this->gc();
		unset($this);
	}
}
?>