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
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']){
			$this->json(P_Lang("插件未启用或不存在"));
		}
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			$this->json(P_Lang('插件应用{appid}.php不存在',array('appid'=>$this->app_id)));
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		$exec = $this->get('exec','system');
		if(!$exec){
			$exec = 'index';
		}
		if(!$mlist || !in_array($exec,$mlist)){
			$this->json(P_Lang('插件方法{method}不存在',array('method'=>$exec)));
		}
		$cls->$exec();
	}

	public function exec_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			error(P_Lang('未指定ID'),'','error');
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']) error('插件不存在或未启用');
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			error(P_Lang('插件应用{appid}.php不存在',array('appid'=>$this->app_id)),'','error');
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		$exec = $this->get('exec','system');
		if(!$exec){
			$exec = 'index';
		}
		if(!$mlist || !in_array($exec,$mlist)){
			error(P_Lang('插件方法{method}不存在',array('method'=>$exec)));
		}
		$cls->$exec();
	}
}
?>