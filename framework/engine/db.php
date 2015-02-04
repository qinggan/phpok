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
	private $prikey = '';
	private $cache;
	private $prefix = 'cache_';
	private $time_use = 0;
	private $time_tmp = 0;
	private $count = 0;
	public function __construct($config=array())
	{
		$this->prikey = md5(serialize($config));
		$this->prefix = 'ok'.substr($this->prikey,0,5).'_';
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
			if(!is_dir($this->config['folder']) || !is_writeable($this->config['folder']))
			{
				$this->status = false;
			}
			if($this->status)
			{
				$this->connect_cache();
			}
		}
	}

	public function __destruct()
	{
		$this->_cache_keysave();
		return true;
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
		$this->_time();
		if($this->config['type'] == 'memcache')
		{
			$this->cache = new Memcache;
			if(!$this->cache->connect($this->config['server'], $this->config['port']))
			{
				$this->_time();
				return false;
			}
		}
		else
		{
			$this->cache = new stdClass;
		}
		$this->cache->keylist = $this->cache_get($this->prikey);
		$this->_time();
		return true;
	}

	//生成cache_id;
	public function cache_id($sql)
	{
		$keyid = $this->prefix.substr(md5($sql),9,24);
		preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.]+)(\s|\()+/isU',$sql,$list);
		$tbl = $list[2] ? $list[2] : array();
		$this->cache->keylist[$keyid] = $tbl;
		return $keyid;
	}

	//读缓存信息
	public function cache_get($id)
	{
		if(!$this->status)
		{
			return false;
		}
		$this->_time();
		$this->_count();
		if($this->config['type'] == 'memcache')
		{
			$info = $this->cache->get($id);
			$this->_time();
			if(!$info)
			{
				return false;
			}
			return unserialize($info);
		}
		if(!is_file($this->config['folder'].$id.'.php') || ((filemtime($this->config['folder'].$id.'.php') + $this->config['time']) < time()))
		{
			$this->_time();
			return false;
		}
		$info = file_get_contents($this->config['folder'].$id.'.php');
		if(!$info)
		{
			$this->_time();
			return false;
		}
		$info = substr($info,15);
		$this->_time();
		if(!$info)
		{
			return false;
		}
		return unserialize($info);
	}

	//存储缓存
	public function cache_save($id='',$data='')
	{
		if(!$this->status || !$id)
		{
			return true;
		}
		$this->_time();
		$this->_count();
		$data = $data ? serialize($data) : '';
		if($this->config_cache['type'] == 'memcache')
		{
			$this->cache->set($id,$data,MEMCACHE_COMPRESSED,$this->config['time']);
		}
		else
		{
			file_put_contents($this->config['folder'].$id.'.php','<?php exit();?>'.$data);
		}
		$this->_time();
		return true;
	}

	//删除缓存
	public function cache_delete($id)
	{
		if(!$this->status || !$id)
		{
			return true;
		}
		$this->_time();
		$this->_count();
		if($this->config_cache['type'] == 'memcache')
		{
			$this->cache->delete($id);
		}
		else
		{
			@unlink($this->config['folder'].$id.'.php');
		}
		$this->_time();
		return true;
	}

	//读取缓存运行时间
	public function cache_time()
	{
		return $this->time_use;
	}

	//缓存运行次数
	public function cache_count()
	{
		return $this->count;
	}

	public function cache_clear($sql='')
	{
		if(!$this->status)
		{
			return false;
		}
		if($sql)
		{
			return $this->_cache_clear($sql);
		}
		$this->_time();
		$this->_count();
		if($this->config['type'] == 'memcache')
		{
			$this->cache->flush();
		}
		else
		{
			$handle = opendir($this->config['folder']);
			while(false !== ($myfile = readdir($handle)))
			{
				if($myfile != "." && $myfile != ".." && is_file($this->config['folder'].$myfile))
				{
					@unlink($this->config['folder'].$myfile);
				}
			}
			closedir($handle);
		}
		$this->_time();
		return true;
	}

	//更新缓存
	private function _cache_clear($sql)
	{
		if(!$this->cache->keylist)
		{
			return true;
		}
		$cacheid = $this->cache_id($sql);
		$tbllist = $this->cache->keylist[$cacheid];
		if(!$tbllist)
		{
			return false;
		}
		foreach($this->cache->keylist as $key=>$value)
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
		if(!$this->status)
		{
			return true;
		}
		$this->cache_save($this->prikey,$this->cache->keylist);
		return true;
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
}
?>