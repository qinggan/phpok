<?php
/***********************************************************
	Filename: plugins/identifier/setting.php
	Note	: 配置参数
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_identifier extends phpok_plugin
{
	var $path;
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace("\\","/",dirname(__FILE__))."/";
	}

	//读取配置文件
	function index()
	{
		return $this->plugin_tpl('setting.html');
	}

	function save()
	{
		//取得插件信息
		$id = $this->plugin_id();
		$ext = array();
		$ext['is_youdao'] = $this->get('is_youdao','checkbox');
		if($ext['is_youdao'])
		{
			$ext['keyfrom'] = $this->get("keyfrom");
			$ext['keyid'] = $this->get("keyid");
		}
		$ext['is_pingyin'] = $this->get('is_pingyin','checkbox');
		$ext['is_py'] = $this->get('is_py','checkbox');
		$this->plugin_save($ext,$id);
	}
}
?>