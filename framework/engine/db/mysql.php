<?php
#==================================================================================================
#	Filename: phpok/engine/db/db_mysql
#	Note	: 连接数据库类
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2011-11-07 12:08
#==================================================================================================

#[类库sql]
class db_mysql
{
	var $query_count = 0;
	var $host;
	var $user;
	var $pass;
	var $data;
	var $conn;
	var $result;
	var $prefix = "qinggan_";
	//返回结果集类型，默认是数字+字符
	var $rs_type = MYSQL_ASSOC;
	var $query_times = 0;#[查询时间]
	var $conn_times = 0;#[连接数据库时间]
	var $unbuffered = false;#[是否不使用结果缓存集查询功能，默认为不使用]
	//定义查询列表
	var $querylist;
	var $debug = false;

	#[构造函数]
	function __construct($config=array())
	{
		$this->host = $config['host'] ? $config['host'] : 'localhost';
		$this->port = $config['port'] ? $config['port'] : '3306';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->data = $config['data'] ? $config['data'] : '';
		$this->debug = $config["debug"] ? $config["debug"] : false;
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		if($this->data)
		{
			$ifconnect = $this->connect($this->data);
			if(!$ifconnect)
			{
				$this->conn = false;
				return false;
			}
		}
		return true;
	}

	#[兼容PHP4]
	function db_mysql($config=array())
	{
		return $this->__construct($config);
	}

	#[连接数据库]
	function connect($database="")
	{
		$start_time = $this->time_used();
		$server = ($this->port && $this->port != "3306") ? $this->host.":".$this->port : $this->host;
		$this->conn = @mysql_connect($server,$this->user,$this->pass,true) or false;
		if(!$this->conn)
		{
			return false;
		}
		$mysql_version = $this->get_version();
		if($mysql_version>"4.1")
		{
			mysql_query("SET NAMES 'utf8'",$this->conn);
		}
		if($mysql_version>"5.0.1")
		{
			mysql_query("SET sql_mode=''",$this->conn);
		}
		$end_time = $this->time_used();
		$this->conn_times += round($end_time - $start_time,5);#[连接数据库的时间]
		$ifok = $this->select_db($database);
		return $ifok ? true : false;
	}

	function select_db($data="")
	{
		$database = $data ? $data : $this->data;
		if(!$database)
		{
			return false;
		}
		$this->data = $database;
		$start_time = $this->time_used();
		$ifok = mysql_select_db($this->data,$this->conn);
		if(!$ifok)
		{
			return false;
		}
		$end_time = $this->time_used();
		$this->conn_times += round($end_time - $start_time,5);#[连接数据库的时间]
		return true;
	}

	#[关闭数据库连接，当您使用持续连接时该功能失效]
	function close()
	{
		if(is_resource($this->conn))
		{
			return mysql_close($this->conn);
		}
		else
		{
			return true;
		}
	}

	function __destruct()
	{
		return $this->close();
	}

	function set($name,$value)
	{
		if($name == "rs_type")
		{
			$value = strtolower($value) == "num" ? MYSQL_NUM : MYSQL_ASSOC;
		}
		$this->$name = $value;
	}

	function query($sql)
	{
		if(!is_resource($this->conn))
		{
			$this->connect();
		}
		else
		{
			 if(!mysql_ping($this->conn))
			{
				 $this->close();
				 $this->connect();
			}
		}
		if($this->debug)
		{
			$sqlkey = md5($sql);
			if($this->querylist)
			{
				$qlist = array_keys($this->querylist);
				if(in_array($sqlkey,$qlist))
				{
					$count = $this->querylist[$sqlkey]["count"] + 1;
					$this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>$count);
				}else{
					$this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>1);
				}
			}
			else{
				$this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>1);
			}
		}
		$start_time = $this->time_used();
		$func = $this->unbuffered && function_exists("mysql_multi_query") ? "mysql_multi_query" : "mysql_query";
		$this->result = $func($sql,$this->conn);
		$this->query_count++;
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		if(!$this->result)
		{
			return false;
		}
		return $this->result;
	}

	function get_all($sql="",$primary="")
	{
		$result = $sql ? $this->query($sql) : $this->result;
		if(!$result)
		{
			return false;
		}
		$start_time = $this->time_used();
		$rs = array();
		$is_rs = false;
		while($rows = mysql_fetch_array($result,$this->rs_type))
		{
			if($primary && $rows[$primary])
			{
				$rs[$rows[$primary]] = $rows;
			}
			else
			{
				$rs[] = $rows;
			}
			$is_rs = true;
		}
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		return ($is_rs ? $rs : false);
	}

	function get_one($sql="")
	{
		$start_time = $this->time_used();
		$result = $sql ? $this->query($sql) : $this->result;
		if(!$result)
		{
			return false;
		}
		$rows = mysql_fetch_array($result,$this->rs_type);
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		return $rows;
	}

	function insert_id($sql="")
	{
		if($sql)
		{
			$rs = $this->get_one($sql);
			return $rs;
		}
		else
		{
			return mysql_insert_id($this->conn);
		}
	}

	function insert($sql)
	{
		$this->result = $this->query($sql);
		$id = $this->insert_id();
		return $id;
	}

	function all_array($table,$condition="",$orderby="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = "SELECT * FROM ".$table;
		if($condition && is_array($condition) && count($condition)>0)
		{
			$sql_fields = array();
			foreach($condition AS $key=>$value)
			{
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$sql .= " WHERE ".implode(" AND ",$sql_fields);
		}
		if($orderby)
		{
			$sql .= " ORDER BY ".$orderby;
		}
		$rslist = $this->get_all($sql);
		return $rslist;
	}

	function one_array($table,$condition="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = "SELECT * FROM ".$table;
		if($condition && is_array($condition) && count($condition)>0)
		{
			$sql_fields = array();
			foreach($condition AS $key=>$value)
			{
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$sql .= " WHERE ".implode(" AND ",$sql_fields);
		}
		$rslist = $this->get_one($sql);
		return $rslist;
	}

	//将数组写入数据中
	function insert_array($data,$table,$insert_type="insert")
	{
		if(!$table || !is_array($data) || !$data)
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		if($insert_type == "insert")
		{
			$sql = "INSERT INTO ".$table;
		}
		else
		{
			$sql = "REPLACE INTO ".$table;
		}
		$sql_fields = array();
		$sql_val = array();
		foreach($data AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`";
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $this->insert($sql);
	}

	//更新数据
	function update_array($data,$table,$condition)
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition))
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}

	function count($sql="")
	{
		if($sql)
		{
			$this->rs_type = MYSQL_NUM;
			$this->query($sql);
			$rs = $this->get_one();
			$this->rs_type = MYSQL_ASSOC;
			return $rs[0];
		}
		else
		{
			return mysql_num_rows($this->result);
		}
	}

	function num_fields($sql="")
	{
		if($sql)
		{
			$this->query($sql);
		}
		return mysql_num_fields($this->result);
	}

	function list_fields($table)
	{
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs)
		{
			return false;
		}
		foreach($rs AS $key=>$value)
		{
			$rslist[] = $value["Field"];
		}
		return $rslist;
	}

	//取得明细的字段管理
	function list_fields_more($tbl)
	{
		$rs = $this->get_all("SHOW COLUMNS FROM ".$tbl);
		if(!$rs)
		{
			return false;
		}
		foreach($rs AS $key=>$value)
		{
			$tmp = array();
			foreach($value AS $k=>$v)
			{
				$tmp[strtolower($k)] = $v;
			}
			$rslist[$value["Field"]] = $tmp;
		}
		return $rslist;
	}

	#[显示表名]
	function list_tables()
	{
		$list = $this->get_all("SHOW TABLES");
		if(!$list)
		{
			return false;
		}
		$rslist = array();
		$id = 'Tables_in_'.$this->data;
		foreach($list AS $key=>$value)
		{
			$rslist[] = $value[$id];
		}
		return $rslist;
	}

	function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	function escape_string($char)
	{
		if(!$char)
		{
			return false;
		}
		return mysql_escape_string($char);
	}

	function get_version()
	{
		return mysql_get_server_info($this->conn);
	}

	function time_used()
	{
		$time = explode(" ",microtime());
		$used_time = $time[0] + $time[1];
		return $used_time;
	}

	//Mysql的查询时间
	function conn_times()
	{
		return $this->conn_times + $this->query_times;
	}

	//MySQL查询资料
	function conn_count()
	{
		return $this->query_count;
	}

	# PHPOK中常用的简洁高效的SQL生成查询，仅适合单表查询
	function phpok_one($tbl,$condition="",$fields="*")
	{
		$sql = "SELECT ".$fields." FROM ".$this->db->prefix.$tbl;
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->get_one($sql);
	}

	function debug()
	{
		if(!$this->querylist || !is_array($this->querylist) || count($this->querylist) < 1)
		{
			return false;
		}
		$html = '<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#CECECE"><tr><td>';
		$html.= '<table cellpadding="1" cellspacing="1" width="100%">';
		$html.= '<tr><th bgcolor="#EFEFEF" height="30px">SQL</th><th bgcolor="#EFEFEF" width="80px">查询</th></tr>';
		foreach($this->querylist AS $key=>$value)
		{
			$html .= '<tr><td bgcolor="#FFFFFF"><div style="padding:3px;color:#6E6E6E;">'.$value['sql'].'</div></td>';
			$html .= '<td align="center" bgcolor="#FFFFFF"><div style="padding:3px;color:#000000;">'.$value["count"].'</div></td></tr>';
		}
		$html.= "</table>";
		$html.= "</td></tr></table>";
		return $html;
	}

	function conn_status()
	{
		if(!$this->conn) return false;
		return true;
	}

}
?>