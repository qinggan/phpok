<?php
/*****************************************************************************************
	文件： {phpok}/engine/db/mysql.php
	备注： MySQL与Cache类集成，后续phpok内核文件之一
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月29日 09时49分
*****************************************************************************************/
class db_mysql extends db
{
	//数据表前缀，很多地方上被直接调用，用public
	public $prefix = 'qinggan_';
	public $conn;
	//是否启用调试
	public $debug = false;
	public $config_db = array('host'=>'localhost','user'=>'root','pass'=>'','data'=>'','port'=>3306);
	private $type = MYSQL_ASSOC;
	private $query; //执行对象
	private $time_use = 0;
	private $time_tmp = 0;
	private $count = 0;

	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->debug = $config["debug"] ? $config["debug"] : false;
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		$this->_config_db($config);
	}

	//连接数据库
	public function connect_db($data='')
	{
		//增加计时器
		$this->_time();
		if(!$data){
			$data = $this->config_db['data'];
		}
		$linkhost = $this->config_db['host'].":".($this->config_db['port'] ? $this->config_db['port'] : '3306');
		if($this->config_db['socket']){
			$linkhost .= ":".$this->config_db['socket'];
		}
		$this->conn = mysql_connect($linkhost,$this->config_db['user'],$this->config_db['pass'],true,MYSQL_CLIENT_COMPRESS);
		if(!$this->conn || !is_resource($this->conn))
		{
			$this->debug('数据库连接失败，错误ID：'.mysql_errno().'，错误信息：'.mysql_error());
		}
		mysql_query("SET NAMES 'utf8'",$this->conn);
		mysql_query("SET sql_mode=''",$this->conn);
		$this->_time();
		$this->select_db($data);
		return true;
	}

	//更换数据库选择
	public function select_db($data="")
	{
		$this->check_connect();
		$this->_time();
		mysql_select_db($data,$this->conn) or die($this->debug());
		$this->_time();
		return true;
	}

	private function check_connect()
	{
		if(!$this->conn || !is_resource($this->conn))
		{
			$this->connect_db();
		}
		else
		{
			if(!mysql_ping($this->conn))
			{
				mysql_close($this->conn);
				$this->connect_db();
			}
		}
	}

	//关闭数据库连接
	public function __destruct()
	{
		parent::__destruct();
		mysql_close($this->conn);
		unset($this);
	}

	//定义基本的变量信息
	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type')
		{
			$value = strtolower($value) == "num" ? MYSQL_NUM : MYSQL_ASSOC;
			$this->type = $value;
		}
		else
		{
			$this->$name = $value;
		}
	}

	public function query($sql)
	{
		if(!$sql || !trim($sql))
		{
			return false;
		}
		$sql = trim($sql);
		$this->check_connect();
		$this->_time();
		$this->query = mysql_query($sql,$this->conn);
		$this->_count();
		$this->_time();
		if(preg_match($this->preg_sql,$sql))
		{
			$this->cache_clear($sql);
		}
		if(!$this->query)
		{
			return false;
		}
		return $this->query;
	}

	public function get_all($sql,$primary="")
	{
		if(!$sql || !trim($sql))
		{
			return false;
		}
		$sql = trim($sql);
		$cache_status = false;
		if(!preg_match($this->preg_sql,$sql))
		{
			$keyid = $this->cache_id($sql);
			$rs = $this->cache_get($keyid);
			if($rs)
			{
				if(is_bool($rs))
				{
					return false;
				}
				if(!$primary)
				{
					return $rs;
				}
				$list = false;
				foreach($rs as $key=>$value)
				{
					$list[$value[$primary]] = $value;
				}
				return $list;
			}
			$cache_status = true;
		}
		$this->query($sql);
		if(!$this->query || !is_resource($this->query))
		{
			return false;
		}
		$this->_time();
		$rs = false;
		while($rows = mysql_fetch_array($this->query,$this->type))
		{
			$rs[] = $rows;
		}
		mysql_free_result($this->query);
		$this->_time();
		if($cache_status && $keyid)
		{
			$this->cache_save($keyid,$rs);
		}
		if($primary && $rs)
		{
			$list = array();
			foreach($rs as $key=>$value)
			{
				$list[$value[$primary]] = $value;
			}
			return $list;
		}
		return $rs;
	}

	//取得单独数据
	public function get_one($sql="")
	{
		if(!$sql || !trim($sql))
		{
			return false;
		}
		$sql = trim($sql);
		$cache_status = false;
		if(!preg_match($this->preg_sql,$sql))
		{
			$keyid = $this->cache_id($sql);
			$rs = $this->cache_get($keyid);
			if($rs)
			{
				if(is_bool($rs))
				{
					return false;
				}
				return $rs;
			}
			$cache_status = true;
		}
		$this->query($sql);
		if(!$this->query || !is_resource($this->query))
		{
			return false;
		}
		$this->_time();
		$rs = mysql_fetch_array($this->query,$this->type);
		mysql_free_result($this->query);
		$this->_time();
		if($cache_status && $keyid)
		{
			$this->cache_save($keyid,$rs);
		}
		return $rs;
	}

	//返回最后插入的ID
	public function insert_id()
	{
		return mysql_insert_id($this->conn);
	}

	//执行写入SQL
	public function insert($sql)
	{
		$this->query($sql);
		return $this->insert_id();
	}

	public function all_array($table,$condition="",$orderby="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = $this->_select_array($table,$condition,$orderby);
		return $this->get_all($sql);
	}

	public function one_array($table,$condition="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = $this->_select_array($table,$condition);
		return $this->get_one($sql);
	}

	//将数组写入数据中
	public function insert_array($data,$table,$insert_type="insert")
	{
		if(!$table || !is_array($data) || !$data)
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		$sql = $this->_insert_array($data,$table,$insert_type);
		return $this->insert($sql);
	}

	//更新数据
	public function update_array($data,$table,$condition)
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition))
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		$sql = $this->_update_array($data,$table,$condition);
		return $this->query($sql);
	}

	public function count($sql="")
	{
		if($sql)
		{
			$this->set('type','num');
			$rs = $this->get_one($sql);
			$this->set('type','assoc');
			return $rs[0];
		}
		else
		{
			return mysql_num_rows($this->query);
		}
	}

	public function num_fields($sql="")
	{
		if($sql)
		{
			$this->query($sql);
		}
		return mysql_num_fields($this->query);
	}

	public function list_fields($table)
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
	public function list_fields_more($tbl)
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

	//显示表明细
	public function list_tables()
	{
		$list = $this->get_all("SHOW TABLES");
		if(!$list)
		{
			return false;
		}
		$rslist = array();
		$id = 'Tables_in_'.$this->config_db['data'];
		foreach($list AS $key=>$value)
		{
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
		if(!$char)
		{
			return false;
		}
		return mysql_real_escape_string($char,$this->conn);
	}

	//数据库查询时间
	public function conn_times()
	{
		return $this->time_use;
	}

	//数据库查询次数
	public function conn_count()
	{
		return $this->count;
	}

	# PHPOK中常用的简洁高效的SQL生成查询，仅适合单表查询
	public function phpok_one($tbl,$condition="",$fields="*")
	{
		$sql = "SELECT ".$fields." FROM ".$this->db->prefix.$tbl;
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->get_one($sql);
	}

	public function conn_status()
	{
		if(!$this->conn) return false;
		return true;
	}

	private function _config_db($config)
	{
		//修正高版本连接数据库慢的Bug
		if (!defined('PHP_VERSION_ID')){
			$version =  explode ('.',PHP_VERSION);
			define('PHP_VERSION_ID',($version[0]*10000 + $version[1]*100 + $version[2]));
		}
		if($config['host'] == 'localhost' && PHP_VERSION_ID >= 50300){
			$config['host'] = '127.0.0.1';
		}
		$this->config_db['host'] = $config['host'] ? $config['host'] : 'localhost';
		$this->config_db['port'] = $config['port'] ? $config['port'] : '3306';
		$this->config_db['user'] = $config['user'] ? $config['user'] : 'root';
		$this->config_db['pass'] = $config['pass'] ? $config['pass'] : '';
		$this->config_db['data'] = $config['data'] ? $config['data'] : '';
		$this->config_db['socket'] = isset($config['socket']) ? $config['socket'] : '';
		if($this->config_db['data']){
			$this->connect_db($this->config_db['data']);
		}
	}

	//缓存运行计时器
	private function _time()
	{
		$time = microtime(true);
		if($this->time_tmp)
		{
			$this->time_use = round(($this->time_use + ($time - $this->time_tmp)),5);
			$this->time_tmp = 0;
		}
		else
		{
			$this->time_tmp = $time;
		}
	}

	//计数器
	private function _count($val=1)
	{
		$this->count += $val;
	}

	//输入Debug错误
	private function debug($info='')
	{
		$errno = mysql_errno($this->conn);
		$error = mysql_error($this->conn);
		if(!$info && $errno && $error)
		{
			$info = '数据请求失败，错误ID：'.$errno."，错误信息是：".$error;
		}
		if($info)
		{
			exit($this->_ascii($info));
		}
		return true;
	}
}
?>