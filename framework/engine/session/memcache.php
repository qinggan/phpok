<?php
/**
 * 基于Memcache的SESSION
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月19日
**/
class session_memcache extends session
{
	private $host = '127.0.0.1';
	private $port = '11211';
	private $prefix = 'sess_';
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		$this->save_path($this->path_dir);
		$this->start();
	}

	public function config($config)
	{
		parent::config($config);
		$this->path_dir = 'tcp://'.$config['host'].":".$config['port'];
		ini_set("session.save_handler", "memcache");
	}
}