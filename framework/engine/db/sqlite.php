<?php
/**
 * 文件型数据库 SQLite 3 引挈，该引挈仅支持 PHP5.3 及更高版本
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年09月08日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db_sqlite extends db
{
	private $type = SQLITE3_ASSOC;
	protected $kec_left = '[';
	protected $kec_right = ']';
	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->config($config);
	}

	public function __destruct()
	{
		$this->close();
		parent::__destruct();
	}

	public function config($config)
	{
		parent::config($config);
	}

	/**
	 * 连接SQLite数据库
	**/
	public function connect()
	{
		if(!$this->database){
			$this->error('数据库文件未设置');
		}
		if(!file_exists($this->database)){
			$this->error('数据库文件不存在');
		}
		$this->_time();
		$this->conn = new SQLite3($this->database,SQLITE3_OPEN_READWRITE);
		$t = get_class_methods($this->conn);
		if(!$this->conn){
			$this->error('数据库连接失败');
		}
		$this->conn->busyTimeout(1000);
		$this->_time();
		$this->query("PRAGMA encoding = 'UTF-8'");
		return $this->conn;
	}

	public function close()
	{
		if($this->conn && is_object($this->conn)){
			$this->conn->close();
		}
	}

	public function type($type='')
	{
		if($type && ($type == 'num' || $type == SQLITE3_NUM)){
			$this->type = SQLITE3_NUM;
		}else{
			$this->type = SQLITE3_ASSOC;
		}
		return $this->type;
	}

	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? SQLITE3_NUM : SQLITE3_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
	}

	/**
	 * 检测链接是否存在
	**/
	private function check_connect()
	{
		if(!$this->conn || !is_object($this->conn)){
			$this->connect();
		}
	}

	public function query($sql)
	{
		$this->check_connect();
		$this->_time();
		$this->query = $this->conn->query($sql);
		$tmptime = $this->_time();
		$this->_count();
		$this->debug($sql,$tmptime);
		if($errid = $this->conn->lastErrorCode()){
			$this->error($this->conn->lastErrorMsg(),$errid);
		}
		$this->cache_update($sql);
		return $this->query;
	}

	public function get_all($sql='',$primary="")
	{
		if($sql){
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = array();
		while($rows = $this->query->fetchArray($this->type)){
			$rs[] = $rows;
		}
		$this->query->finalize();
		$this->_time();
		if(!$rs){
			return false;
		}
		$rs = $this->decode($rs);
		if($primary && !is_bool($primary)){
			$tlist = array();
			foreach($rs as $key=>$value){
				$tlist[$value[$primary]] = $value;
			}
			$rs = $tlist;
			unset($tlist);
		}
		return $rs;
	}

	public function get_one($sql="")
	{
		if($sql){
			$rs = $this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = $this->query->fetchArray($this->type);
		$this->_time();
		$this->query->finalize();
		if(!$rs){
			return false;
		}
		$rs = $this->decode($rs);
		return $rs;
	}

	public function insert_id()
	{
		$this->check_connect();
		return $this->conn->lastInsertRowID();
	}

	/**
	 * 查看有多少数量
	 * @参数 $sql 要查询的SQL
	 * @参数 $is_count 是否使用 SQL 自带的 count 
	 * @参数 
	**/
	public function count($sql="",$is_count=true)
	{
		if($sql && $is_count){
			$this->set('type','num');
			$rs = $this->get_one($sql);
			$this->set('type','assoc');
			return $rs[0];
		}else{
			if($sql){
				$this->query($sql);
			}
			if($this->query){
				$this->query->reset();
				$i = 0;
				while($rows = $this->query->fetch_array($this->type)){
					$i++;
				}
				return $i;
			}
			return false;
		}
	}

	/**
	 * 查询字段个数
	 * @参数 $sql 要查询的语句
	**/
	public function num_fields($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if($this->query){
			return $this->query->numColumns();
		}
		return false;
	}

	public function list_fields($table)
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "PRAGMA table_info(".$table.")";
		$rslist = $this->get_all($sql);
		if(!$rslist){
			return false;
		}
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$tmplist[] = $value['name'];
		}
		return $tmplist;
	}

	public function list_fields_more($table)
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "PRAGMA table_info(".$table.")";
		$rslist = $this->get_all($sql);
		if(!$rslist){
			return false;
		}
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$tmp = array('Field'=>$value['name'],'Type'=>$value['type'],'NULL'=>'NO','Key'=>'','Default'=>$value['dflt_value']);
			if(!$value['notnull']){
				$tmp['NULL'] = 'YES';
			}
			if($value['pk']){
				$tmp['Key'] = 'PRI';
			}
			$tmplist[$key] = $value;
		}
		return $tmplist;
	}

	public function list_tables()
	{
		$rslist = $this->get_all("SELECT tbl_name FROM sqlite_master WHERE type='table'");
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$rs[] = $value["tbl_name"];
		}
		return $rs;
	}

	//显示表名
	public function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	public function table_create($table,$idlist)
	{
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
	}

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

	/**
	 * 取得版本号
	**/
	public function version($type="server")
	{
		return $this->conn->version();
	}

	public function decode($char)
	{
		if(!$char){
			return false;
		}
		if(is_array($char)){
			foreach($char as $key=>$value){
				if($value){
					$char[$key] = $this->decode($value);
				}
			}
		}else{
			$char = str_replace("\'\'", "'", $char);
			$char = str_replace('\"', '"', $char);
		}
		return $char;
	}

	public function escape_string($char)
	{
		return $this->encode($char);
	}

	public function encode($char)
	{
		if(!$char){
			return false;
		}
		if(is_array($char)){
			foreach($char as $key=>$value){
				if($value){
					$char[$key] = $this->encode($value);
				}
			}
		}else{
			$char = $this->conn->escapeString(stripslashes($char));
		}
		return $char;
	}
}