<?php
/***********************************************************
	Filename: phpok/engine/session/file.php
	Note	: 自定义SESSION存储目录
	Version : 4.0
	Author  : qinggan
	Update  : 2011-11-07 15:54
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class session_file extends session
{
	public $path_dir = "./data/session/";
	public $sys_time;

	public function __construct($config)
	{
		if(!$config || !is_array($config)){
			$config["id"] = "PHPSESSID";
			$config["path"] = ROOT."data/session/";
			$config["timeout"] = 600;
		}
		if(!$config['id']){
			$config["id"] = "PHPSESSID";
		}
		if(!$config['path']){
			$config['path'] = ROOT.'data/session/';
		}
		if(!$config['timeout']){
			$config["timeout"] = 600;
		}
		parent::__construct($config);
		$this->config($config);
		$this->save_path($this->path_dir);
		$this->start();
	}

	public function config($config)
	{
		$this->path_dir = $config['path'] ? $config['path'] : ROOT.'data/session/';
	}
}
?>