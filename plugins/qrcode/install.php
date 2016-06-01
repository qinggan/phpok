<?php
/*****************************************************************************************
	文件： plugins/qrcode/install.php
	备注： 二维码安装器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月16日 21时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_qrcode extends phpok_plugin
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
		$ext['wh'] = $this->get('wh');
		$ext['logo'] = $this->get('logo');
		$this->plugin_save($ext,$id);
	}
}

?>