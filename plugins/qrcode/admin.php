<?php
/*****************************************************************************************
	文件： plugins/qrcode/admin.php
	备注： 二维码管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月17日 00时03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_qrcode extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	function clear()
	{
		$this->lib('file')->rm($this->dir_root.'data/cache/');
		$this->json(true);
	}
}

?>