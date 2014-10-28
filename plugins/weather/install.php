<?php
/*****************************************************************************************
	文件： plugins/weather/install.php
	备注： 站长Sitemap地图
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年6月2日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_weather extends phpok_plugin
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

	function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['address'] = $this->get('address');
		$ext['ip_api'] = $this->get('ip_api');
		$ext['weather_api'] = $this->get('weather_api');
		$this->plugin_save($ext,$id);
	}
}

?>