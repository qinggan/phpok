<?php
/*****************************************************************************************
	文件： plugins/duanxincm/setting.php
	备注： 配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 12时49分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_duanxincm extends phpok_plugin
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
		$ext['cm_account'] = $this->get('cm_account');
		$ext['cm_password'] = $this->get('cm_password');
		$ext['cm_server'] = $this->get('cm_server');
		$ext['cm_check_code'] = $this->get('cm_check_code');
		$this->plugin_save($ext,$id);
	}
}

?>