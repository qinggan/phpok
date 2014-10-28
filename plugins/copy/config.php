<?php
/***********************************************************
	Filename: plugins/copy/config.php
	Note	: 复制主题功能
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:12
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class config_copy
{
	var $app;
	var $tpl;
	var $id;
	var $path;
	function __construct($app,$id)
	{
		$this->id = $id;
		$this->path = $GLOBALS['app']->dir_root."plugins/".$id."/";
	}

	//配置翻译参数
	function setting($rs)
	{
		$GLOBALS['app']->assign("rs",$rs);
		return $GLOBALS['app']->fetch($this->path."setting.html","abs-file");
	}

	//存储翻译参数
	function save($rs)
	{
		$max_count = $GLOBALS['app']->get("max_count");
		$rs["max_count"] = $max_count;
		return $rs;
	}

	//勾子
	//命名规则
	//admin->后台ID，前台用www
	//cate_control->控制器
	//set->执行的函数
	function admin_list_action()
	{
		$plugin_rs = $GLOBALS['app']->model('plugin')->get_one($this->id);
		$GLOBALS['app']->assign("plugin_rs",$plugin_rs);
		return $GLOBALS['app']->fetch($this->path."button.html","abs-file");
	}

	function copy_id()
	{
		$count = $GLOBALS['app']->get("count","int");
		if(!$count) $count = 5;
		if($count>10) $count = 10;
		$ids = $GLOBALS['app']->get("ids");
		if(!$ids)
		{
			json_exit("未指定要复制的主题ID");
		}
		$list = explode(",",$ids);
		foreach($list AS $key=>$value)
		{
			$value = intval($value);
			if($value)
			{
				for($i=0;$i<$count;$i++)
				{
					$GLOBALS['app']->model('list')->copy_id($value);
				}
			}
		}
		json_exit("复制成功",true);
	}
}
?>