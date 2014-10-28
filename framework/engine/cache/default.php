<?php
/***********************************************************
	Filename: libs/autoload/cache.php
	Note	: 缓存类，仅支持 txt 和 memcache 两种模式
	Version : 3.0
	Author  : qinggan
	Update  : 2012-05-08 10:59
***********************************************************/
class cache_default
{
	//缓存类型
	var $cache_type = "txt";
	var $cache_folder = "cache/";
	var $cache_status = false;
	var $cache_time = 1800;
	var $file_count = 0;
	//缓存前缀
	var $prefix = "qinggan_";

	function __construct($config)
	{
		$this->config($config);
	}

	function config($config)
	{
		//if(!$config['status']) return false;
		$this->cache_status = true;
		$this->cache_time = $config['timeout'] ? $config['timeout'] : 600;
		$this->cache_folder = $config["folder"] ? $config["folder"] : str_replace("\\","/",dirname(__FILE__))."/../../../data/cache/";
		$this->prefix = $config["prefix"] ? $config["prefix"] : "qinggan_";
		if(substr($this->cache_folder,-1) != "/") $this->cache_folder .= "/";
		if(!is_dir($this->cache_folder))
		{
			exit("Cache directory:".$this->cache_folder." Does not exist.");
		}
	}

	//结束进程时
	function __destruct()
	{
		//
	}

	function status($ifopen="")
	{
		if($ifopen != "")
		{
			$this->cache_status = $ifopen ? true : false;
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

	function write($key,$value)
	{
		if(!$this->cache_status || !$key || !$value) return false;
		$value = serialize($value);
		$file = $this->cache_folder.$key.".php";
		file_put_contents($file,'<?php exit();?>'.$value);
		$this->file_count++;
	}

	//生成KEY,
	//site_id，网站ID
	//ext,扩展值
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

	//取得缓存列表
	function key_list($site_id="",$ext="")
	{
		if(!$this->cache_status) return false;
		$handle = opendir($this->cache_folder);
		$rslist = array();
		while(false !== ($myfile = readdir($handle)))
		{
			if(is_file($this->cache_folder.$myfile))
			{
				$t = substr($myfile,0,-4);
				if(strlen($t) == 32)
				{
					$rslist[] = $t;
				}
			}
		}
		if(!$site_id && !$ext) return $rslist;
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

	//删除缓存
	function delete($key="")
	{
		if(!$this->cache_status) return false;
		if(!$key) return $this->clear();
		unlink($this->cache_folder.$key.".php");
	}

	//读取缓存操作
	function read($key)
	{
		if(!$this->cache_status || !$key) return false;
		$cache_file = $this->cache_folder.$key.".php";
		if(!is_file($cache_file)) return false;
		$file_time = filemtime($cache_file);
		if( ($file_time + $this->cache_time) < time() )
		{
			$this->delete($key);
			return false;
		}
		$content = file_get_contents($cache_file);
		$this->file_count++;
		$content = substr($content,15);
		if(!$content)
		{
			return false;
		}
		return unserialize($content);
	}

	//清除缓存
	function clear()
	{
		if(!$this->cache_status) return false;
		$handle = opendir($this->cache_folder);
		$array = array();
		while(false !== ($myfile = readdir($handle)))
		{
			if(is_file($this->cache_folder.$myfile))
			{
				unlink($this->cache_folder.$myfile);
			}
		}
		closedir($handle);
	}

	function count()
	{
		return $this->file_count;
	}
}
?>