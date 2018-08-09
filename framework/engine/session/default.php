<?php
/**
 * SESSION默认引挈
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_default extends session
{
	public $path_dir = '';
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
	}
}