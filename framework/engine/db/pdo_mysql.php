<?php
/*****************************************************************************************
	文件： pdo_mysql.php
	备注： PDO连接MySQL操作类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月13日 22时17分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class db_pdo_mysql extends db
{
	private $host = '127.0.0.1';
	private $user = 'root';
	private $pass = '';
	private $port = 3306;
	private $socket = '';
	private $type = PDO::FETCH_ASSOC;

	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->config($config);
	}

	public function config($config)
	{
		parent::config($config);
		if (!defined('PHP_VERSION_ID')){
			$version =  explode ('.',PHP_VERSION);
			define('PHP_VERSION_ID',($version[0]*10000 + $version[1]*100 + $version[2]));
		}
		if($config['host'] && $config['host'] == 'localhost' && PHP_VERSION_ID >= 50300){
			$config['host'] = '127.0.0.1';
		}
		$this->host = $config['host'] ? $config['host'] : '127.0.0.1';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->port = $config['port'] ? $config['port'] : 3306;
		$this->socket = $config['socket'] ? $config['socket'] : '';
	}


	public function host($host='')
	{
		if($host){
			$this->host = $host;
		}
		return $this->host;
	}

	public function user($user='')
	{
		if($user){
			$this->user = $user;
		}
		return $this->user;
	}

	public function pass($pass='')
	{
		if($pass){
			$this->pass = $pass;
		}
		return $this->pass;
	}

	public function port($port='')
	{
		if($port){
			$this->port = $port;
		}
		return $this->port;
	}

	public function socket($socket='')
	{
		if($socket){
			$this->socket = $socket;
		}
		return $this->socket;
	}

	public function type($type='')
	{
		if($type && $type == 'num'){
			$this->type = PDO::FETCH_NUM;
		}else{
			$this->type = PDO::FETCH_ASSOC;
		}
		return $this->type;
	}

	public function connect()
	{
		$this->_time();
		$dsn = 'mysql:host='.$this->host.';dbname='.$this->database.';port='.$this->port;
		if($this->socket){
			$dsn .= ';unix_socket='.$this->socket;
		}
		try{
			$this->conn = new PDO($dsn,$this->user,$this->pass);
		} catch(PDOException $e){
			$this->error('数据库连接失败，错误信息：'.$e->getMessage());
		}
		$this->conn->exec("SET NAMES 'utf8'");
		$this->conn->exec("SET sql_mode=''");
		$this->conn->setAttribute(PDO::ATTR_CASE,PDO::CASE_NATURAL);
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		$this->_time();
		return true;
	}

	//检测连接
	private function check_connect()
	{
		if(!$this->conn || !is_object($this->conn)){
			$this->connect();
		}else{
			$status = $this->conn->getAttribute(PDO::ATTR_SERVER_INFO);
			if($status == 'MySQL server has gone away'){
				$this->connect();
			}
		}
		if(!$this->conn || !is_object($this->conn)){
			$this->error('数据库连接失败');
		}
		return true;
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//定义基本的变量信息
	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
	}

	public function query($sql,$loadcache=true)
	{
		if($this->debug){
			$this->debug($sql);
		}
		if($loadcache){
			$this->cache_sql($sql);
		}
		$this->check_connect();
		$this->_time();
		$this->query = $this->conn->query($sql);
		if($loadcache){
			$this->cache_update($sql);
		}
		$this->_time();
		$this->_count();
		return $this->query;
	}

	public function get_all($sql,$primary="")
	{
		$this->query($sql);
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = false;
		while($rows = $this->query->fetch($this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		$this->query->closeCursor();
		$this->_time();
		return $rs;
	}

	public function get_one($sql="")
	{
		$this->query($sql);
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = $this->query->fetch($this->type);
		$this->query->closeCursor();
		$this->_time();
		return $rs;
	}

	//返回最后插入的ID
	public function insert_id()
	{
		$this->check_connect();
		return $this->conn->lastInsertId();
	}

	//写入操作
	public function insert($sql,$tbl='',$type='insert')
	{
		if(is_array($sql) && $tbl){
			return $this->insert_array($sql,$tbl,$type);
		}
		$this->query($sql);
		return $this->insert_id();
	}

	public function insert_array($data,$tbl,$type="insert")
	{
		if(!$tbl || !$data || !is_array($data)){
			return false;
		}
		if(substr($tbl,0,strlen($this->prefix)) != $this->prefix){
			$tbl = $this->prefix.$tbl;
		}
		$type = strtolower($type);
		$sql = $type == 'insert' ? "INSERT" : "REPLACE";
		$sql.= " INTO ".$tbl." ";
		$sql_fields = array();
		$sql_val = array();
		foreach($data AS $key=>$value){
			$sql_fields[] = "`".$key."`";
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $this->insert($sql);
	}

	//更新操作
	public function update($data,$tbl='',$condition='')
	{
		if(is_array($data) && $tbl && $condition){
			return $this->update_array($data,$tbl,$condition);
		}
		return $this->query($data);
	}

	//删除操作
	public function delete($table,$condition='')
	{
		if(!$condition || !$table){
			return false;
		}
		if(is_array($condition)){
			$sql_fields = array();
			foreach($condition AS $key=>$value){
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$condition = implode(" AND ",$sql_fields);
			if(!$condition){
				return false;
			}
		}
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DELETE FROM ".$table." WHERE ".$condition;
		return $this->query($sql);
	}

	//更新数据
	public function update_array($data='',$table='',$condition='')
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition)){
			return false;
		}
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data AS $key=>$value){
			$sql_fields[] = "`".$key."`='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition AS $key=>$value){
			$sql_fields[] = "`".$key."`='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}

	public function count($sql="")
	{
		if($sql){
			$this->set('type','num');
			$rs = $this->get_one($sql);
			$this->set('type','assoc');
			return $rs[0];
		}else{
			if($this->query){
				return $this->query->rowCount();
			}
			return false;
		}
	}

	public function num_fields($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if($this->query){
			return $this->query->columnCount();
		}
		return false;
	}

	public function list_fields($table)
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs AS $key=>$value){
			$rslist[] = $value["Field"];
		}
		return $rslist;
	}

	//取得明细的字段管理
	public function list_fields_more($table)
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs AS $key=>$value){
			$tmp = array();
			foreach($value AS $k=>$v){
				$tmp[strtolower($k)] = $v;
			}
			$rslist[$value["Field"]] = $tmp;
		}
		return $rslist;
	}

	//显示表明细
	public function list_tables()
	{
		$list = $this->get_all("SHOW TABLES");
		if(!$list){
			return false;
		}
		$rslist = array();
		$id = 'Tables_in_'.$this->database;
		foreach($list AS $key=>$value){
			$rslist[] = $value[$id];
		}
		return $rslist;
	}

	//显示表名
	public function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	public function escape_string($char)
	{
		if(!$char){
			return false;
		}
		$this->check_connect();
		return $this->conn->quote($char);
	}

	//PHPOK中常用的简洁高效的SQL生成查询，仅适合单表查询
	public function phpok_one($tbl,$condition="",$fields="*")
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "SELECT ".$fields." FROM ".$table;
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->get_one($sql);
	}
}
?>