<?php
/***********************************************************
	Filename: {phpok}/www/plugin_control.php
	Note	: 插件中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:04
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
		$this->exec_f();
	}

	//执行JS
	function exec_f()
	{
		$id = $this->get("id");
		if(!$id) $this->json(1002);
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs) json_exit(1001);
		if($rs['param']) $rs['param'] = unserialize($rs['param']);
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')) $this->json(1008);
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$methods = get_class_methods($cls);
		$exec = $this->get("exec");
		if(!$exec) $exec = 'index';
		if(!$methods || !in_array($exec,$methods)) $this->json(1009);
		$this->assign('plugin_rs',$rs);
		$cls->$exec($rs);
	}

	function ajax_f()
	{
		$this->exec_f();
	}
}
?>