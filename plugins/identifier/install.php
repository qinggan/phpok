<?php
/***********************************************************
	Filename: plugins/identifier/install.php
	Note	: 有道翻译插件安装
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_identifier extends phpok_plugin
{
	var $path;
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace("\\","/",dirname(__FILE__)).'/';
	}

	function index()
	{
		return $this->plugin_tpl('setting.html',$id);
	}

	//存储安装配置
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