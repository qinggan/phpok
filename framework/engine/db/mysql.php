<?php
/*****************************************************************************************
	文件： {phpok}/engine/db/mysql.php
	备注： MySQL连接引挈，后续phpok内核文件之一
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月29日 09时49分
*****************************************************************************************/
class db_mysql extends db
{
	private $host = '127.0.0.1';
	private $user = 'root';
	private $pass = '';
	private $port = 3306;
	private $socket = '';
	private $type = MYSQL_ASSOC;

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
		if($type && ($type == 'num' || $type == MYSQL_NUM)){
			$this->type = MYSQL_NUM;
		}else{
			$this->type = MYSQL_ASSOC;
		}
		return $this->type;
	}

	public function connect()
	{
		$this->_time();
		$host = $this->host.':'.$this->port;
		if($this->socket){
			$host .= ':'.$this->socket;
		}
		$this->conn = @mysql_connect($host,$this->user,$this->pass,true,MYSQL_CLIENT_COMPRESS);
		if(!$this->conn || !is_resource($this->conn)){
			$this->error(mysql_error(),mysql_errno());
		}
		mysql_query("SET NAMES 'utf8'",$this->conn);
		mysql_query("SET sql_mode=''",$this->conn);
		mysql_select_db($this->database,$this->conn);
		if(mysql_error($this->conn)){
			$this->error(mysql_error($this->conn),mysql_errno($this->conn));
		}
		$this->_time();
		return $this->conn;
	}

	//检测链接是否存在
	private function check_connect()
	{
		if(!$this->conn || !is_resource($this->conn)){
			$this->connect();
		}else{
			if(!mysql_ping($this->conn)){
				mysql_close($this->conn);
				$this->connect();
			}
		}
		if(!$this->conn || !is_resource($this->conn)){
			$this->error('数据库连接失败');
		}
	}

	public function __destruct()
	{
		parent::__destruct();
		if($this->conn && is_resource($this->conn)){
			mysql_close($this->conn);
		}
		unset($this);
	}

	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? MYSQL_NUM : MYSQL_ASSOC;
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
		$this->query = mysql_unbuffered_query($sql,$this->conn);
		if($loadcache){
			$this->cache_update($sql);
		}
		$this->_time();
		$this->_count();
		if(mysql_error($this->conn)){
			$this->error(mysql_error($this->conn),mysql_errno($this->conn));
		}
		return $this->query;
	}

	public function get_all($sql,$primary="")
	{
		$this->query($sql);
		if(!$this->query || !is_resource($this->query)){
			return false;
		}
		$this->_time();
		$rs = false;
		while($rows = mysql_fetch_array($this->query,$this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		mysql_free_result($this->query);
		$this->_time();
		return $rs;
	}

	public function get_one($sql="")
	{
		$this->query($sql);
		if(!$this->query || !is_resource($this->query)){
			return false;
		}
		$this->_time();
		$rs = mysql_fetch_array($this->query,$this->type);
		mysql_free_result($this->query);
		$this->_time();
		return $rs;
	}

	//返回最后插入的ID
	public function insert_id()
	{
		$this->check_connect();
		return mysql_insert_id($this->conn);
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
				return mysql_num_rows($this->query);
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
			return mysql_num_fields($this->query);
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
		return mysqli_escape_string($char,$this->conn);
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