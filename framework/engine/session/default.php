<?php
/**
 * SESSION默认引挈
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年12月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_default extends session
{
	public $path_dir = '';
	public $save_handler = 'files';
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		if($this->path_dir){
			$this->save_path($this->path_dir);
		}
		$this->start();
	}

	public function config($config)
	{
		parent::config($config);
		$this->path_dir = $config['path'] ? $config['path'] : '';
		if($config['save_handler'] == 'redis' || $config['save_handler'] == 'memcache'){
			ini_set("session.save_handler", $config['save_handler']);
		}
	}
}