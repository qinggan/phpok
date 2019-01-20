<?php
/**
 * 文件型数据库 SQLite 3 引挈，该引挈仅支持 PHP5.3 及更高版本
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
	
	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->config($config);
	}

	public function __destruct()
	{
		$this->close();
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
		if(!$this->conn){
			$this->error('数据库连接失败');
		}
		$this->conn->busyTimeout(100);
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

	public function query($sql,$loadcache=true)
	{
		if($loadcache){
			$this->cache_sql($sql);
		}
		$this->check_connect();
		$this->_time();
		$this->query = $this->conn->query($sql);
		if($loadcache){
			$this->cache_update($sql);
		}
		$tmptime = $this->_time();
		$this->_count();
		$this->debug($sql,$tmptime);
		if($errid = $this->conn->lastErrorCode()){
			$this->error($this->conn->lastErrorMsg(),$errid);
		}
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
		while($rows = $this->query->fetchArray($this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		$this->query->finalize();
		if($rs){
			$rs = $this->decode($rs);
		}
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
		$rs = $this->query->fetchArray($this->type);
		$this->query->finalize();
		if($rs){
			$rs = $this->decode($rs);
		}
		$this->_time();
		return $rs;
	}

	public function insert_id()
	{
		$this->check_connect();
		return $this->conn->lastInsertRowID();
	}

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
		$sql.= " INTO [".$tbl."] ";
		$sql_fields = array();
		$sql_val = array();
		foreach($data as $key=>$value){
			$sql_fields[] = "[".$key."]";
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $this->insert($sql);
	}

	public function update($data,$tbl='',$condition='')
	{
		if(is_array($data) && $tbl && $condition){
			return $this->update_array($data,$tbl,$condition);
		}
		return $this->query($data);
	}

	public function update_array($data='',$table='',$condition='')
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition)){
			return false;
		}
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "UPDATE [".$table."] SET ";
		$sql_fields = array();
		foreach($data as $key=>$value){
			$sql_fields[] = "[".$key."]='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition as $key=>$value){
			$sql_fields[] = "[".$key."]='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}

	public function delete($table,$condition='')
	{
		if(!$condition || !$table){
			return false;
		}
		if(is_array($condition)){
			$sql_fields = array();
			foreach($condition as $key=>$value){
				$sql_fields[] = "[".$key."]='".$value."' ";
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