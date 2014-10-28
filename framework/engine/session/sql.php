<?php
/***********************************************************
	Filename: phpok/engine/session/sql.php
	Note	: 将SESSION放在数据库中
	Version : 4.0
	Author  : qinggan
	Update  : 2011-11-07 15:54
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class session_sql
{
	var $db;
	var $table;
	var $sessid;
	var $sys_time;
	var $timeout = 3600;
	var $config;

	function __construct($db,$config)
	{
		$this->sys_time = time();
		if(!$config || !is_array($config))
		{
			$config["session_id"] = "PHPSESSID";
			$config["session_table"] = "session";
			$config["session_timeout"] = 3600;
		}
		$this->config = $config;
		$this->timeout = $config["session_timeout"];
		$this->db = $db;
		$sid = $config["session_id"];
		$this->sid = $sid;
		$session_id = isset($_POST[$sid]) ? $_POST[$sid] : (isset($_GET[$sid]) ? $_GET[$sid] : "");
		if($session_id)
		{
			session_id($session_id);
			$this->sessid = $session_id;
		}
		session_set_save_handler
		(
			array($this,"open"),
			array($this,"close"),
			array($this,"read"),
			array($this,"write"),
			array($this,"destory"),
			array($this,"gc")
		);
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('public');
		session_start();
	}

	function open($save_path,$session_name)
	{
		$this->prefix = $this->db->prefix;
		$this->table = $this->prefix.$this->config["session_table"];
		return true;
	}

	function close()
	{
		return true;
	}

	function read($sid="")
	{
		$this->sessid = $sid;
		$rs = $this->db->get_one("SELECT * FROM ".$this->table." WHERE id='".$sid."'");
		if(!$rs)
		{
			$sql = "INSERT INTO ".$this->table."(id,data,lasttime) VALUES('".$sid."','','".$this->sys_time."')";
			$this->db->query($sql);
			return false;
		}
		else
		{
			if(!$rs["data"])
			{
				return false;
			}
			return $rs["data"];
		}
	}

	function write($sid,$data)
	{
		$this->db->query("UPDATE ".$this->table." SET data='".$data."',lasttime='".$this->sys_time."' WHERE id='".$sid."'");
		return true;
	}

	function destory($sid)
	{
		$this->db->query("DELETE FROM ".$this->table." WHERE id='".$sid."'");
		return true;
	}

	function gc()
	{
		$this->db->query("DELETE FROM ".$this->table." WHERE lasttime+".$this->timeout."<'".$this->sys_time."'");
		return true;
	}

	function sessid($sid="")
	{
		if($sid) $this->sessid = $sid;
		if(!$this->sessid) $this->sessid = session_id();
		return $this->sessid;
	}

	function __destruct()
	{
		$this->gc();
		return true;
	}

	function sid()
	{
		return $this->sid;
	}
}
?>