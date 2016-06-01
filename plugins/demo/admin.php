<?php
/*****************************************************************************************
	文件： plugins/demo/admin.php
	备注： 测试插件后台的应用
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月20日 23时21分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_demo extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	public function html_cate_index_foot()
	{
		$this->assign('demo_1',$this->me['param']['demo_1']);
		echo $this->plugin_tpl('admin.html');
	}
}
?>