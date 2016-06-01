<?php
/*****************************************************************************************
	文件： libs/debug.php
	备注： Debug调试使用类
	版本： 5.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月20日 09时47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class debug_lib
{
	public $time_point = 0;
	public $memory_point = 0;
	public $time_use = 0;
	public $memory_use = 0;
	
	public function __construct()
	{
		//
	}

	public function start()
	{
		$this->time_point = microtime(true);
		$this->memory_point = memory_get_usage();
	}

	public function stop()
	{
		$time = round((microtime(true) - $this->time_point),5);
		$memory = round((memory_get_usage() - $this->memory_point),5);
		$memory = $this->memory_format($memory);
		return array('time'=>$time,'memory'=>$memory);
	}

	private function memory_format($memory)
	{
		if($memory <= 1024){
			$memory = "1 KB";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)." KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)." MB";
		}
		return $memory;
	}
}
?>