<?php
/***********************************************************
	Filename: {phpok}/api/plugin_control.php
	Note	: 插件获取JSON内容数据
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		//判断是否有插件ID
		$id = $this->get('id','system');
		if(!$id) $this->json(1017);
		//判断插件是否存在
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']) $this->json(1018);
		//判断插件文件是否存在
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')) $this->json(1019);
		//装载插件
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		$exec = $this->get('exec','system');
		if(!$exec) $exec = 'index';
		if(!$mlist || !in_array($exec,$mlist)) $this->json(1020);
		$cls->$exec();
	}

	function exec_f()
	{
		$id = $this->get('id','system');
		if(!$id) error('未指定要执行的插件！');
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']) error('插件不存在或未启用');
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php'))
		{
			error('插件文件：plugins/'.$id.'/'.$this->app_id.'.php 不存在！');
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		$exec = $this->get('exec','system');
		if(!$exec) $exec = 'index';
		if(!$mlist || !in_array($exec,$mlist))
		{
			error('执行方法：'.$exec.' 不存在！');
		}
		$cls->$exec();
	}
}
?>