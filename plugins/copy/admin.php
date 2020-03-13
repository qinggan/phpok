<?php
/***********************************************************
	Filename: plugins/copy/admin.php
	Note	: 复制主题功能
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:12
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_copy extends phpok_plugin
{
	var $path;
	public function __construct()
	{
		parent::plugin();
		$this->path = str_replace('\\','/',dirname(__FILE__)).'/';
	}

	//勾子
	//命名规则
	//admin->后台ID，前台用www
	//cate_control->控制器
	//set->执行的函数
	function html_list_action_body()
	{
		$this->tpl->assign('plugin_rs',$this->plugin_info());
		echo $this->plugin_tpl('button.html');
	}

	public function copy_id()
	{
		$count = $this->get("count","int");
		if(!$count) $count = 5;
		if($count>10) $count = 10;
		$ids = $this->get("ids");
		if(!$ids){
			$this->error("未指定要复制的主题ID");
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			$value = intval($value);
			if($value){
				for($i=0;$i<$count;$i++){
					$this->model('list')->copy_id($value);
				}
			}
		}
		$this->success();
	}
}