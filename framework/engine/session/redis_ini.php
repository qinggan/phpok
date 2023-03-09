<?php
/**
 * 基于 Redis 的SESSION
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 6.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2022年12月8日
**/
class session_redis_ini extends session
{
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		$this->save_path($this->path_dir);
		$this->start();
		return true;
	}
	
	public function config($config)
	{
		parent::config($config);
		if(!$config['path']){
			$this->error("未设置好存储路径");
		}
		$this->path_dir = $config['path'];
		ini_set('session.save_handler','redis');
	}
}