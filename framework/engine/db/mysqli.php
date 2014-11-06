<?php
/*****************************************************************************************
	文件： {phpok}/engine/db/mysqli.php
	备注： MySQL与Cache类集成，后续phpok内核文件之一
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月29日 09时49分
*****************************************************************************************/
class db_mysqli
{
	//统计执行次数及时间
	public $stat_info;
	//数据表前缀，很多地方上被直接调用，用public
	public $prefix = 'qinggan_';
	public $conn;
	//是否启用调试
	public $debug = false;
	private $config_db = array('host'=>'localhost','user'=>'root','pass'=>'','data'=>'','port'=>3306);
	private $config_cache = array('type'=>'file','folder'=>'cache/','time'=>3600,'server'=>'localhost','port'=>11211);
	private $type = MYSQLI_ASSOC;
	private $query; //执行对象
	private $cache;//缓存对象
	private $cache_keyid;
	private $cache_prikey = 'phpok_prikey';//缓存的主字段
	private $cache_list; //当前已加载的缓存，防止可以免去多次数读取
	private $time;

	public function __construct($config=array())
	{
		$this->time = time();
		//初始化调试
		$this->debug = $config["debug"] ? $config["debug"] : false;
		//初始化数据表前缀
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		//初始化统计计数
		$this->_stat_info();
		//初始化数据库配置
		$this->_config_db($config);
		//初始化缓存引挈
		$this->_config_cache($config);
		return true;
	}

	//连接数据库
	public function connect_db($data='')
	{
		//增加计时器
		$this->timer('sql');
		if(!$data)
		{
			$data = $this->config_db['data'];
		}
		$host = $this->config_db['host'];
		$user = $this->config_db['user'];
		$pass = $this->config_db['pass'];
		$port = $this->config_db['port'];
		$socket = $this->config_db['socket'];
		$this->conn = new mysqli($host,$user,$pass,'',$port,$socket);
		if($this->conn->connect_error)
		{
			$this->debug('数据库连接失败，错误ID：'.$this->conn->connect_errno.'，错误信息：'.$this->conn->connect_error);
		}
		if($this->conn->error)
		{
			$this->debug();
		}
		if(!$this->conn || !is_object($this->conn))
		{
			$this->debug('数据库连接请求失败');
		}
		$this->conn->query("SET NAMES 'utf8'");
		$this->conn->query("SET sql_mode=''");
		$this->timer('sql');
		$this->select_db($data);
		//$this->counter('sql',2); //计数器;
		return true;
	}
	//更换数据库选择
	public function select_db($data="")
	{
		$this->timer('sql');
		$this->conn->select_db($data);
		if($this->conn->error)
		{
			$this->debug();
		}
		$this->timer('sql');
		return true;
	}

	//关闭数据库连接
	public function __destruct()
	{
		//关闭数据库连接
		$this->_cache_keysave();
		$this->conn->close();
	}

	//连接缓存
	public function connect_cache()
	{
		if(!$this->config_cache['status'])
		{
			return true;
		}
		if($this->config_cache['type'] == 'memcache')
		{
			$this->timer('memcache');
			$this->cache = new Memcache;
			if(!$this->cache->connect($this->config_cache['server'], $this->config_cache['port']))
			{
				return false;
			}
			$this->timer('memcache');
		}
		else
		{
			$this->cache = new stdClass;
		}
		$this->cache->phpok_keylist = $this->cache_get($this->cache_prikey);
		return true;
	}

	//定义基本的变量信息
	public function set($name,$value)
	{
		if($name == "rs_type" || name == 'type')
		{
			$value = strtolower($value) == "num" ? MYSQLI_NUM : MYSQLI_ASSOC;
			$this->type = $value;
		}
		else
		{
			$this->$name = $value;
		}
	}

	public function query($sql)
	{
		if(!$this->conn || !is_object($this->conn))
		{
			$this->connect();
		}
		else
		{
			if(!$this->conn->ping())
			{
				$this->conn->close();
				$this->connect();
			}
		}
		$this->timer('sql');
		$this->query = $this->conn->query($sql);
		$this->counter('sql');
		//清除缓存
		if(!preg_match('/^SELECT/isU',$sql))
		{
			$this->_cache_clear($sql);
		}
		$this->timer('sql');
		if(!$this->query)
		{
			return false;
		}
		return $this->query;
	}

	//生成cache_id;
	public function cache_id($sql)
	{
		$keyid = 'phpok_'.md5($sql);
		preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.]+)(\s|\()+/isU',$sql,$list);
		$tbl = $list[2] ? $list[2] : array();
		$this->cache->phpok_keylist[$keyid] = $tbl;
		return $keyid;
	}

	//读缓存信息
	public function cache_get($id)
	{
		if(!$this->config_cache['status'])
		{
			return false;
		}
		if($this->config_cache['type'] == 'memcache')
		{
			$this->counter('memcache');
			$this->timer('memcache');
			$info = $this->cache->get($id);
			$this->timer('memcache');
			if(!$info)
			{
				return false;
			}
		}
		else
		{
			if(!is_file($this->config_cache['folder'].$id.'.php'))
			{
				return false;
			}
			//判断最后修改时间，超时直接返回
			if((filemtime($this->config_cache['folder'].$id.'.php') + $this->config_cache['time']) < $this->time)
			{
				return false;
			}
			$this->timer('file');
			$this->counter('file');
			$info = file_get_contents($this->config_cache['folder'].$id.'.php');
			$this->timer('file');
			if(!$info)
			{
				return false;
			}
			$info = substr($info,15);
		}
		return unserialize($info);
	}

	public function cache_clear()
	{
		if(!$this->config_cache['status'])
		{
			return false;
		}
		if($this->config_cache['type'] == 'memcache')
		{
			$this->timer('memcache');
			$this->counter('memcache');
			$this->cache->flush();
			$this->timer('memcache');
		}
		else
		{
			$this->timer('file');
			$this->counter('file');
			$handle = opendir($this->config_cache['folder']);
			$array = array();
			while(false !== ($myfile = readdir($handle)))
			{
				if($myfile != "." && $myfile != ".." && is_file($this->config_cache['folder'].$myfile))
				{
					@unlink($this->config_cache['folder'].$myfile);
				}
			}
			closedir($handle);
			$this->timer('file');
		}
		return true;
	}

	//存储缓存
	public function cache_save($id='',$data='')
	{
		if(!$this->config_cache['status'] || !$id || !$data)
		{
			return false;
		}
		$data = serialize($data);
		if($this->config_cache['type'] == 'memcache')
		{
			$this->timer('memcache');
			$this->counter('memcache');
			$this->cache->set($id,$data,MEMCACHE_COMPRESSED,$this->config_cache['time']);
			$this->timer('memcache');
		}
		else
		{
			$this->timer('file');
			$this->counter('file');
			file_put_contents($this->config_cache['folder'].$id.'.php','<?php exit();?>'.$data);
			$this->timer('file');
		}
		return true;
	}

	//删除缓存
	public function cache_delete($id)
	{
		if(!$this->config_cache['status'] || !$id)
		{
			return false;
		}
		if($this->config_cache['type'] == 'memcache')
		{
			$this->timer('memcache');
			$this->counter('memcache');
			$this->cache->delete($id);
			$this->timer('memcache');
			return true;
		}
		//删除文件
		$this->timer('file');
		$this->counter('file');
		@unlink($this->config_cache['folder'].$id.'.php');
		$this->timer('file');
		return true;
	}
	
	public function get_all($sql,$primary="")
	{
		//如果存在缓存，优先读缓存
		$keyid = $this->cache_id($sql);
		$rs = $this->cache_get($keyid);
		if($rs)
		{
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
		$this->query($sql);
		if(!$this->query || !is_object($this->query))
		{
			return false;
		}
		$this->timer('sql');
		$rs = false;
		while($rows = $this->query->fetch_array($this->type))
		{
			$rs[] = $rows;
		}
		$this->timer('sql');
		$this->cache_save($keyid,$rs);
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
		if(!$sql)
		{
			return false;
		}
		$keyid = $this->cache_id($sql);
		$rs = $this->cache_get($keyid);
		if($rs)
		{
			return $rs;
		}
		$this->query($sql);
		if(!$this->query || !is_object($this->query))
		{
			return false;
		}
		$this->timer('sql');
		$rs = $this->query->fetch_array($this->type);
		if($rs)
		{
			$this->cache_save($keyid,$rs);
		}
		$this->timer('sql');
		return $rs;
	}

	//返回最后插入的ID
	public function insert_id()
	{
		return $this->conn->insert_id;
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
		return $this->get_all($sql);
	}

	public function one_array($table,$condition="")
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
	public function update_array($data,$table,$condition)
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

	public function count($sql="")
	{
		if($sql)
		{
			$this->type = MYSQLI_NUM;
			$rs = $this->get_one($sql);
			$this->type = MYSQLI_ASSOC;
			return $rs[0];
		}
		else
		{
			return $this->query->num_rows;
		}
	}

	public function num_fields($sql="")
	{
		if($sql)
		{
			$this->query($sql);
		}
		return $this->query->field_count;
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
		return $this->conn->escape_string($char);
	}

	//数据库查询时间
	public function conn_times()
	{
		return $this->stat_info['sql']['time'];
	}

	//数据库查询次数
	public function conn_count()
	{
		return $this->stat_info['sql']['count'];
	}

	//读取缓存运行时间
	public function cache_time()
	{
		return round(($this->stat_info['file']['time'] + $this->stat_info['memcache']['time']),5);
	}

	//缓存运行次数
	public function cache_count()
	{
		return round(($this->stat_info['file']['count'] + $this->stat_info['memcache']['count']));
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

	private function _ascii($str='')
	{
		if(!$str) return false;
		$str = iconv("UTF-8", "UTF-16BE", $str);
		$output = "";
		for ($i = 0; $i < strlen($str); $i++,$i++)
		{
			$code = ord($str{$i}) * 256 + ord($str{$i + 1});
			if ($code < 128)
			{
				$output .= chr($code);
			}
			else if($code != 65279)
			{
				$output .= "&#".$code.";";
			}
		}
		return $output;
	}


	//初始化统计
	private function _stat_info()
	{
		//初始化统计
		$this->stat_info['sql'] = array('time'=>0,'count'=>0);
		$this->stat_info['file'] = array('time'=>0,'count'=>0);
		$this->stat_info['memcache'] = array('time'=>0,'count'=>0);
	}
	
	//初始化缓存
	private function _config_cache($config)
	{
		$this->cache_prikey = md5(serialize($config));
		if(!isset($config['cache']))
		{
			$this->config_cache['status'] = false;
			return true;
		}
		$this->config_cache['status'] = isset($config['cache']['status']) ? $config['cache']['status'] : false;
		if(!$this->config_cache['status'])
		{
			return true;
		}
		$this->config_cache['type'] = isset($config['cache']['type']) ? $config['cache']['type'] : 'file';
		$this->config_cache['folder'] = isset($config['cache']['folder']) ? $config['cache']['folder'] : 'cache/';
		$this->config_cache['server'] = isset($config['cache']['server']) ? $config['cache']['server'] : 'localhost';
		$this->config_cache['port'] = isset($config['cache']['port']) ? $config['cache']['port'] : 11211;
		$this->config_cache['time'] = isset($config['cache']['time']) ? $config['cache']['time'] : 36000;
		//判断类型，如果不符合条件，则使用file类型
		if(!in_array($this->config_cache['type'],array('file','memcache')))
		{
			$this->config_cache['type'] = 'file';
		}
		//当环境不支持memcache时，返回file做缓存
		if($this->config_cache['type'] == 'memcache' && !class_exists('Memcache'))
		{
			$this->config_cache['type'] = 'file';
		}
		//检测Memcache服务器连接
		if($this->config_cache['type'] == 'memcache' && !$this->connect_cache())
		{
			$this->config_cache['type'] = 'file';
		}
		if($this->config_cache['type'] == 'file')
		{
			if(!is_dir($this->config_cache['folder']) || !is_writeable($this->config_cache['folder']))
			{
				$this->config_cache['status'] = false;
			}
			if($this->config_cache['status'])
			{
				$this->connect_cache();
			}
		}
		return true;
	}
	
	//初始化数据库
	private function _config_db($config)
	{
		$this->config_db['host'] = $config['host'] ? $config['host'] : 'localhost';
		$this->config_db['port'] = $config['port'] ? $config['port'] : '3306';
		$this->config_db['user'] = $config['user'] ? $config['user'] : 'root';
		$this->config_db['pass'] = $config['pass'] ? $config['pass'] : '';
		$this->config_db['data'] = $config['data'] ? $config['data'] : '';
		$this->config_db['socket'] = isset($config['socket']) ? $config['socket'] : '';
		if($this->config_db['data'])
		{
			$this->connect_db($this->config_db['data']);
		}
	}
	//内部计时器
	//id仅支持三种参数，sql，file和memcache
	private function timer($id='')
	{
		if(!$id)
		{
			return false;
		}
		$time = explode(" ",microtime());
		$time = $time[0] + $time[1];
		if($this->stat_info[$id] && isset($this->stat_info[$id]['tmp']))
		{
			$time = round(($this->stat_info[$id]['time'] + ($time - $this->stat_info[$id]['tmp'])),5);
			$this->stat_info[$id]['time'] = $time;
			unset($this->stat_info[$id]['tmp']);
		}
		else
		{
			$this->stat_info[$id]['tmp'] = $time;
		}
	}

	//计数器
	private function counter($id='',$val=1)
	{
		if(!$id)
		{
			return false;
		}
		$this->stat_info[$id]['count'] += $val;
	}

	//输入Debug错误
	private function debug($info='')
	{
		if(!$info && $this->conn && $this->conn->error)
		{
			$info = '数据请求失败，错误ID：'.$this->conn->errno."，错误信息是：".$this->conn->error;
		}
		if($info)
		{
			exit($this->_ascii($info));
		}
		return true;
	}

	//更新缓存
	private function _cache_clear($sql)
	{
		if(!$this->cache->phpok_keylist)
		{
			return true;
		}
		$cacheid = $this->cache_id($sql);
		$tbllist = $this->cache->phpok_keylist[$cacheid];
		if(!$tbllist)
		{
			return false;
		}
		foreach($this->cache->phpok_keylist as $key=>$value)
		{
			if(!$value || !is_array($value))
			{
				continue;
			}
			$tmp = array_intersect($tbllist,$value);
			if($tmp && count($tmp)>0)
			{
				$this->cache_delete($key);
			}
		}
		return true;
	}

	private function _cache_keysave()
	{
		if(!$this->config_cache['status'])
		{
			return false;
		}
		$this->cache_save($this->cache_prikey,$this->cache->phpok_keylist);
		return true;
	}
}
?>