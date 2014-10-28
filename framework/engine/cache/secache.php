<?php
/***********************************************************
	Filename: {phpok}/engine/cache/secache.php
	Note	: secache单文件缓存
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class cache_secache
{
	var $obj;
	var $cache_status = true;
	var $cache_time = 60;
	var $secache_dir;
	var $keylist = '';
	function __construct($config)
	{
		$this->secache_dir = str_replace("\\",'/',dirname(__FILE__)).'/secache/';
		$this->config($config);
	}

	function __destruct()
	{
		if($this->keylist)
		{
			foreach($this->keylist AS $key=>$value)
			{
				if(!$value || !trim($value))
				{
					unset($this->keylist[$key]);
					continue;
				}
				$this->keylist[$key] = str_replace("\n","",$value);
			}
			$this->keylist = array_unique($this->keylist);
			$msg = implode("\n",$this->keylist);
			file_put_contents($this->cache_folder."cachedata_index.php",$msg);
		}
		return true;
	}

	function config($config)
	{
		$this->cache_folder = $config["folder"] ? $config["folder"] : str_replace("\\","/",dirname(__FILE__))."/../../../data/cache/";
		$this->cache_status = $config["status"];
		$this->cache_time = $config["timeout"] ? $config["timeout"] : 600;
		//连接se缓存
		$this->connect_secache();
	}

	function connect_secache()
	{
		if(!$this->cache_status) return false;
		include_once($this->secache_dir.'secache.php');
		$this->obj = new secache;
		$this->obj->workat($this->cache_folder.'cachedata');
		//检测索引文件
		if(!is_file($this->cache_folder.'cachedata_index.php')) touch($this->cache_folder.'cachedata_index.php');
		$this->keylist = file($this->cache_folder.'cachedata_index.php');
	}

	function status($ifopen='')
	{
		if($ifopen != "")
		{
			$this->cache_status = $ifopen ? true : false;
			$this->connect_secache();
		}
		return $this->cache_status;
	}

	function write($key,$value)
	{
		if(!$this->cache_status) return false;
		if(!$key || !$value) return false;
		$tkey = md5($key);
		$this->keylist[] = $key;
		$this->obj->store($tkey,$value);
		return true;
	}

	function cache_time($time="")
	{
		if($time)
		{
			$this->cache_time = $time;
			return $time;
		}
		else
		{
			return $this->cache_time;
		}
	}

	function key($var="",$site_id="",$ext="")
	{
		if(!$this->cache_status) return false;
		if(!$var) $var = time();
		if(is_array($var) || is_object($var)) $var = serialize($var);
		$str = md5($this->prefix."_".$var);
		$tmp = "";
		if($ext)
		{
			$ext = str_replace(array(",","-"," "),"_",$ext);
			$tmp .= "_".$ext;
		}
		if($site_id) $tmp .= "_".$site_id;
		$chk_length = strlen($tmp);
		if($chk_length > 0 && $chk_length < 28)
		{
			$str = substr($str,0,(32 - $chk_length));
			$str.= $tmp;
		}
		$str = str_replace(array("\n","\t","\r"),"",$str);
		return $str; 
	}
	

	function key_list($site_id="",$ext="")
	{
		if(!$this->keylist) return false;
		$this->keylist = array_unique($this->keylist);
		//格式化，检测出相应的key
		$chk = array();
		if($site_id) $chk[] = $site_id;
		if($ext)
		{
			$extlist = explode(",",$ext);
			$extlist = array_unique($extlist);
			foreach($extlist AS $key=>$value)
			{
				if($value && trim($value)) $chk[] = $value;
			}
		}
		$chk = array_unique($chk);
		//当返回为空时，返回全部数据
		if(!$chk || count($chk) < 1) return $this->keylist;
		$list = array();
		foreach($this->keylist AS $key=>$value)
		{
			if(!$value || !trim($value))
			{
				unset($this->keylist[$key]);
				continue;
			}
			$tmp = explode("_",trim($value));
			$tmp = array_intersect($tmp,$chk);
			if($tmp && count($tmp)>0)
			{
				$list[] = $value;
				continue;
			}
		}
		if($list && count($list)>0) return $list;
		return false;
	}

	//删除
	function delete($key)
	{
		if(!$key) return false;
		$tkey = md5($key);
		$this->obj->delete($tkey);
		if($this->keylist)
		{
			$id = array_search($key,$this->keylist);
			if($id !== false)
			{
				unset($this->keylist[$id]);
			}
		}
		return true;
	}

	function read($key)
	{
		$value = "";
		$tkey = md5($key);
		if(!$this->keylist || ($this->keylist && !in_array($key,$this->keylist))) return false;
		$this->obj->fetch($tkey,$value);
		return $value;
	}

	function clear()
	{
		$this->obj->clear();
		file_put_contents($this->cache_folder."cachedata_index.php",'');
		return true;
	}

	function count()
	{
		return 1;
	}

	function delete_keywords($site_id="",$ext="")
	{
		if(!$this->cache_status) return false;
		$list = $this->key_list($site_id,$ext);
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$this->delete($value);
			}
		}
	}
}
?>