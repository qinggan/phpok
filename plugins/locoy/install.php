<?php
/*****************************************************************************************
	文件： plugins/locoy/install.php
	备注： 火车头采集器数据扩展项
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月16日 21时40分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_locoy extends phpok_plugin
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
		return $this->plugin_tpl('setting.html');
	}

	public function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['locoy_thumbfile'] = $this->get('locoy_thumbfile');
		$ext['locoy_thumb'] = $this->get('locoy_thumb');
		$this->plugin_save($ext,$id);
	}
}

?>