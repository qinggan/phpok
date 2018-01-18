<?php
/**
 * Debug调试使用类
 * @package phpok\framework\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年11月27日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class debug_lib
{
	private $time_point = 0;
	private $memory_point = 0;
	private $time_node;
	private $memory_node;
	
	public function start($name='')
	{
		$this->time_point = microtime(true);
		$this->memory_point = memory_get_usage();
		if($name){
			$this->time_node[$name] = $this->time_point;
			$this->memory_node[$name] = $this->memory_point;
		}
		return true;
	}

	public function stop($name='')
	{
		$time = microtime(true);
		$memory = memory_get_usage();
		$used_time = round(($time - $this->time_point),5);
		$used_memory = round(($memory - $this->memory_point),5);
		if($name && $this->time_node[$name] && $this->memory_node[$name]){
			$used_time = round(($time - $this->time_node[$name]),5);
			$used_memory = round(($memory - $this->memory_node[$name]),5);
		}
		$used_memory = $this->memory_format($used_memory);
		return array('time'=>$used_time,'memory'=>$used_memory);
	}

	public function all()
	{
		return array('time'=>$this->time_node,'memory'=>$this->memory_node);
	}

	private function memory_format($memory)
	{
		if($memory <= 1024){
			$memory = $memory." B";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)." KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)." MB";
		}
		return $memory;
	}
}