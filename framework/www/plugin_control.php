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

	public function index_f()
	{
		$this->exec_f();
	}

	public function exec_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			error(P_Lang('未指定要执行的插件'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']){
			error(P_Lang('插件不存在或未启用'));
		}
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			error(P_Lang('插件文件：{file}不存在',array('file'=>'<span class="red">plugins/'.$id.'/'.$this->app_id.'.php</span>')));
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		$exec = $this->get('exec','system');
		if(!$exec) $exec = 'index';
		if(!$mlist || !in_array($exec,$mlist)){
			error('执行方法：'.$exec.' 不存在！');
		}
		$this->assign('plugin_rs',$rs);
		$cls->$exec();
	}

	public function ajax_f()
	{
		$this->exec_f();
	}
}
?>