<?php
/*****************************************************************************************
	文件： {phpok}/engine/db.php
	备注： DB基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月04日 09时55分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class db
{
	private $config = array('type'=>'file','folder'=>'cache/','time'=>3600,'server'=>'localhost','port'=>11211);
	private $status = false;
	public $cache_prikey = '';
	private $cache;
	private $prefix = 'cache_';
	public $cache_time_use = 0;
	public $cache_time_tmp = 0;
	public $cache_count = 0;
	protected $preg_sql = '/^(UPDATE|DELETE|REPLACE|INSERT)/isU';
	public $cache_keylist;
	
	public function __construct($config=array())
	{
		$this->cache_prikey = md5(serialize($config));
		$this->prefix = 'ok'.substr($this->cache_prikey,0,5).'_';
		if(!isset($config['cache']))
		{
			$this->status = false;
			return true;
		}
		$this->status = isset($config['cache']['status']) ? $config['cache']['status'] : false;
		if(!$this->status)
		{
			return true;
		}
		$this->config['type'] = isset($config['cache']['type']) ? $config['cache']['type'] : 'file';
		$this->config['folder'] = isset($config['cache']['folder']) ? $config['cache']['folder'] : 'cache/';
		$this->config['server'] = isset($config['cache']['server']) ? $config['cache']['server'] : 'localhost';
		$this->config['port'] = isset($config['cache']['port']) ? $config['cache']['port'] : 11211;
		$this->config['time'] = isset($config['cache']['time']) ? $config['cache']['time'] : 36000;
		//判断类型，如果不符合条件，则使用file类型
		if(!in_array($this->config['type'],array('file','memcache')) || ($this->config['type'] == 'memcache' && !class_exists('Memcache')) || ($this->config['type'] == 'memcache' && !$this->connect_cache()))
		{
			$this->config['type'] = 'file';
		}
		if($this->config['type'] == 'file')
		{
			if(!is_dir($this->config['folder']) || !is_writeable($this->config['folder'])){
				$this->status = false;
			}
			if($this->status){
				$this->connect_cache();
			}
		}
	}

	public function __destruct()
	{
		$this->cache_time_use = $this->cache_time_tmp = $this->cache_count = 0;
		$this->_cache_keysave();
		if($this->config['type'] == 'memcache')
		{
			$this->cache->close();
		}
		unset($this);
	}

	//关闭缓存
	public function cache_close()
	{
		$this->status = false;
	}

	//开启缓存
	public function cache_open()
	{
		$this->status = true;
	}

	public function cache_status()
	{
		return $this->status;
	}

	//连接缓存
	public function connect_cache()
	{
		if(!$this->status)
		{
			return true;
		}
		$this->_time_cache();
		if($this->config['type'] == 'memcache')
		{
			$this->cache = new Memcache;
			if(!$this->cache->connect($this->config['server'], $this->config['port']))
			{
				$this->_time_cache();
				return false;
			}
		}
		else
		{
			$this->cache = new stdClass;
		}
		$this->cache_keylist = $this->cache_get($this->cache_prikey);
		if(!$this->cache_keylist || ($this->cache_keylist && is_bool($this->cache_keylist)))
		{
			$this->cache_keylist = array();
		}
		$this->_time_cache();
		return true;
	}

	//生成cache_id;
	public function cache_id($sql,$tbl='')
	{
		if(!$this->status)
		{
			return false;
		}
		$keyid = $this->prefix.substr(md5($sql),9,24);
		if(!$tbl)
		{
			preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
			$tbl = $list[2] ? $list[2] : false;
		}
		else
		{
			if(is_string($tbl))
			{
				$tbl = explode(",",$tbl);
			}
		}
		if(!$tbl)
		{
			return false;
		}
		$this->cache_keylist[$keyid] = $tbl;
		return $keyid;
	}

	//读缓存信息
	public function cache_get($id)
	{
		if(!$this->status || !$id)
		{
			return false;
		}
		$this->_time_cache();
		$this->_count_cache();
		if($this->config['type'] == 'memcache')
		{
			$info = $this->cache->get($id);
			$this->_time_cache();
			if(!$info)
			{
				return false;
			}
			if($info == '<\-|phpok|-/>')
			{
				return true;
			}
			return unserialize($info);
		}
		if(!is_file($this->config['folder'].$id.'.php') || ((filemtime($this->config['folder'].$id.'.php') + $this->config['time']) < time()))
		{
			$this->_time_cache();
			return false;
		}
		$info = file_get_contents($this->config['folder'].$id.'.php');
		if(!$info)
		{
			$this->_time_cache();
			return false;
		}
		$info = substr($info,15);
		$this->_time_cache();
		if(!$info)
		{
			return false;
		}
		if($info == '<\-|phpok|-/>')
		{
			return true;
		}
		return unserialize($info);
	}

	//存储缓存
	public function cache_save($id='',$data='')
	{
		if(!$this->status || !$id)
		{
			return false;
		}
		$this->_time_cache();
		$this->_count_cache();
		$data = $data ? serialize($data) : '<\-|phpok|-/>';
		if($this->config['type'] == 'memcache')
		{
			$this->cache->set($id,$data,MEMCACHE_COMPRESSED,$this->config['time']);
		}
		else
		{
			file_put_contents($this->config['folder'].$id.'.php','<?php exit();?>'.$data);
		}
		$this->_time_cache();
		return true;
	}

	//删除缓存
	public function cache_delete($id)
	{
		if(!$this->status || !$id)
		{
			return true;
		}
		$this->_time_cache();
		$this->_count_cache();
		if($this->config['type'] == 'memcache')
		{
			$this->cache->delete($id);
		}
		else
		{
			@unlink($this->config['folder'].$id.'.php');
		}
		$this->_time_cache();
		return true;
	}

	//读取缓存运行时间
	public function cache_time()
	{
		return $this->cache_time_use;
	}

	//缓存运行次数
	public function cache_count()
	{
		return $this->cache_count;
	}

	private function debug($info)
	{
		$file = ROOT.'data/db_debug.txt';
		$handle = fopen($file,'ab');
		fwrite($handle,$info."\n");
		fclose($handle);
	}

	final public function cache_clear($sql='')
	{
		if(!$this->status){
			return false;
		}
		if($sql){
			return $this->_cache_clear($sql);
		}
		$this->_time_cache();
		$this->_count_cache();
		if($this->config['type'] == 'memcache'){
			$this->cache->flush();
		}else{
			$handle = opendir($this->config['folder']);
			while(false !== ($myfile = readdir($handle))){
				if($myfile != "." && $myfile != ".." && is_file($this->config['folder'].$myfile)){
					@unlink($this->config['folder'].$myfile);
				}
			}
			closedir($handle);
		}
		$this->_time_cache();
		return true;
	}

	//更新缓存
	private function _cache_clear($sql)
	{
		if(!$this->cache_keylist){
			return true;
		}
		$cacheid = $this->cache_id($sql);
		$tbllist = $this->cache_keylist[$cacheid];
		if(!$tbllist){
			return false;
		}
		foreach($this->cache_keylist as $key=>$value){
			if(!$value || !is_array($value)){
				continue;
			}
			$tmp = array_intersect($tbllist,$value);
			if($tmp && count($tmp)>0){
				$this->cache_delete($key);
			}
		}
		return true;
	}

	protected function _cache_keysave()
	{
		if(!$this->status)
		{
			return true;
		}
		$data = $this->cache_keylist ? serialize($this->cache_keylist) : '<\-|phpok|-/>';
		if($this->config['type'] == 'memcache')
		{
			$this->cache->set($this->cache_prikey,$data,MEMCACHE_COMPRESSED,$this->config['time']);
		}
		else
		{
			file_put_contents($this->config['folder'].$this->cache_prikey.'.php','<?php exit();?>'.$data);
		}
		return true;
	}

	//缓存运行计时器
	final public function _time_cache()
	{
		$time = microtime(true);
		if($this->cache_time_tmp)
		{
			$this->cache_time_use = round(($this->cache_time_use + ($time - $this->cache_time_tmp)),5);
			$this->cache_time_tmp = 0;
		}
		else
		{
			$this->cache_time_tmp = $time;
		}
	}

	//计数器
	final public function _count_cache($val=1)
	{
		$this->cache_count += $val;
	}

	public function _ascii($str='')
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

	protected function _insert_array($data,$table,$insert_type="insert")
	{
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
		return $sql;
	}

	protected function _select_array($table,$condition="",$orderby="")
	{
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
		else
		{
			if($condition && is_string($condition))
			{
				$sql .= " WHERE ".$condition." ";
			}
		}
		if($orderby)
		{
			$sql .= " ORDER BY ".$orderby;
		}
		return $sql;
	}

	protected function _update_array($data,$table,$condition)
	{
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
		return $sql;
	}
}
?>