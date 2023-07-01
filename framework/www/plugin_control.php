<?php
/**
 * 插件中心
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年11月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class plugin_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 插件前台方法
	**/
	public function index_f()
	{
		$id = $this->get('_phpokid','id');
		if(!$id){
			$id = $this->get('id','id');
			if(!$id){
				$this->error(P_Lang('未指定ID'));
			}
		}
		$exec = $this->get('exec','system');
		$this->action($id,$exec);
	}

	/**
	 * 插件方法别名
	**/
	public function exec_f()
	{
		$this->index_f();
	}

	/**
	 * 插件方法别名
	**/
	public function ajax_f()
	{
		$this->index_f();
	}

	public function action($id,$exec="index",$params=array())
	{
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$exec){
			$exec = 'index';
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']){
			$this->error(P_Lang('插件不存在或未启用'));
		}
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			$this->error(P_Lang('插件应用{appid}.php不存在',array('appid'=>$this->app_id)));
		}
		//将传过来的参数变量变成Post模式，以方便内部执行
		if($params && is_array($params)){
			foreach($params as $key=>$value){
				$_POST[$key] = $value;
			}
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		if(!$mlist || !in_array($exec,$mlist)){
			$this->error(P_Lang('插件方法{method}不存在',array('method'=>$exec)));
		}
		$cls->$exec($params);
	}
}