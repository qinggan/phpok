<?php
/*****************************************************************************************
	文件： plugins/demo/install.php
	备注： 测试插件安装脚本
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月20日 20时57分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_demo extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	public function index()
	{
		return $this->plugin_tpl('install.html');
	}

	public function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['demo_1'] = $this->get('demo_1');
		$this->plugin_save($ext,$id);
	}
}

?>