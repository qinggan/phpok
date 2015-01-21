<?php
/*****************************************************************************************
	文件： plugins/sitemap/setting.php
	备注： 站长Sitemap地图设置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年6月2日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_sitemap extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	function index()
	{
		return $this->plugin_tpl('setting.html');
	}

	//存储扩展表单内容
	function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['changefreq'] = $this->get('changefreq');
		$this->plugin_save($ext,$id);
	}
}

?>