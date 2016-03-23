<?php
/***********************************************************
	Filename: {phpok}engine/session/default.php
	Note	: SESSION默认引挈
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月4日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_default extends session
{
	public $path_dir = '';
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
		$this->path_dir = $config['path'] ? $config['path'] : '';
	}
}
?>