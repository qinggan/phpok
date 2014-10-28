<?php
/***********************************************************
	Filename: {phpok}/engine/cache/memcache.php
	Note	: Memcache引挈
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cache_memcache
{
	var $conn;
	var $cache_conn;
	var $cache_status = false;
	var $cache_time = 60;
	var $cache_folder = "../cache/";
	var $cache_index = "memkey.php";
	var $prefix = "qinggan_";
	var $key_list = "";
	var $server = 'localhost';
	var $port = '11211';
	function __construct($config)
	{
		$this->config($config);
	}

	function config($config)
	{
		$this->server = $config["server"] ? $config["server"] : "localhost";
		$this->port = $config["port"] ? $config["port"] : "11211";
		$this->prefix = $config["prefix"] ? $config["prefix"] : "qinggan_";
		$this->cache_folder = $config["folder"] ? $config["folder"] : str_replace("\\","/",dirname(__FILE__))."/../../../data/cache/";
		if(substr($this->cache_folder,-1) != "/") $this->cache_folder .= "/";
		$this->cache_index = $this->cache_folder."memkey.php";
		$this->cache_status = $config["status"];
		$this->cache_time = $config["timeout"] ? $config["timeout"] : 600;
		//如果启用缓存，则连接缓存服务器
		if($this->cache_status) $this->cache_start();
	}

	function cache_start()
	{
		$this->conn = new Memcache;
		$this->conn->connect($this->server,$this->port) OR exit("Can not connect to memcached server, please check.");
		if(is_file($this->cache_folder."memkey.php"))
		{
			$this->key_list = file($this->cache_index);
		}
	}

	//结束缓存时执行
	function __destruct()
	{
		//禁用缓存时，直接跳出
		if(!$this->cache_status) return true;
		if($this->key_list && is_array($this->key_list) && count($this->key_list)>0)
		{
			$keylist = array();
			foreach($this->key_list AS $key=>$value)
			{
				if($value && trim($value))
				{
					$keylist[] = trim($value);
				}
			}
			$keylist = array_unique($keylist);
			$msg = implode("\n",$keylist);
			file_put_contents($this->cache_index,$msg);
		}
	}

	//设置缓存状态
	function status($ifopen="")
	{
		if($ifopen != "")
		{
			$this->cache_status = $ifopen ? true : false;
			if($this->cache_status) $this->cache_start();
		}
		return $this->cache_status;
	}

	function time($time="")
	{
		if($time)
		{
			$this->cache_time = $time;
		}
		return $this->cache_time;
	}

	//写入缓存信息
	function write($key,$value)
	{
		if($this->cache_status && $this->conn && is_object($this->conn))
		{
			$this->conn->set($key,$value,0,$this->time());
			$this->key_list[] = $key;
			$this->key_list = array_unique($this->key_list);
			return true;
		}
		return false;
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
		return $str; 
	}

	function key_list($site_id="",$ext="")
	{
		if(!$this->cache_status) return false;
		$rslist = $this->key_list;
		if(!$rslist) return false;
		//格式化，检测出相应的key
		$chk = array();
		if($site_id) $chk[] = $site_id;
		if($ext)
		{
			$ext = str_replace(array("_","-","|"),",",$ext);
			$extlist = explode(",",$ext);
			foreach($extlist AS $key=>$value)
			{
				$chk[] = $value;
			}
		}
		$chk = array_unique($chk);
		if(!$chk || count($chk) < 1)
		{
			return $rslist;
		}
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			$tmp = explode("_",$value);
			if(array_intersect($tmp,$chk))
			{
				$list[] = $value;
			}
		}
		if($list && count($list)>0) return $list;
		return false;
	}

	//删除指定关键词的缓存
	//site_id，网站ID
	//ext，扩展关键字，多个关键字用英文逗号隔开
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

	function delete($key="")
	{
		if(!$this->cache_status) return false;
		if(!$key) return $this->clear();
		if($this->cache_status && $this->conn && is_object($this->conn))
		{
			$this->conn->delete($key);
			if($this->key_list && is_array($this->key_list) && count($this->key_list)>0)
			{
				$k = array_search($key,$this->key_list);
				if($k !== false)
				{
					unset($this->key_list[$k]);
				}
			}
			return true;
		}
		return false;
	}

	function read($key)
	{
		if(!$this->cache_status) return false;
		if($this->cache_status && $this->conn && is_object($this->conn))
		{
			$content = $this->conn->get($key);
			return $content;
		}
		return false;
	}

	function clear()
	{
		if(!$this->cache_status) return false;
		if($this->conn && is_object($this->conn))
		{
			$this->conn->flush();
			unset($this->key_list);
		}
		return true;
	}

	function count()
	{
		return 2;
	}

}
?>