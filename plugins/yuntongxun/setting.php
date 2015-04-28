<?php
/*****************************************************************************************
	文件： plugins/yuntongxun/setting.php
	备注： 配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 12时49分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_yuntongxun extends phpok_plugin
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
		$ext['ytx_account_sid'] = $this->get('ytx_account_sid');
		$ext['ytx_account_token'] = $this->get('ytx_account_token');
		$ext['ytx_app_id'] = $this->get('ytx_app_id');
		$ext['ytx_sever_ip'] = $this->get('ytx_sever_ip');
		$ext['ytx_server_port'] = $this->get('ytx_server_port');
		$ext['ytx_soft_version'] = $this->get('ytx_soft_version');
		$ext['ytx_check_code'] = $this->get('ytx_check_code');
		$this->plugin_save($ext,$id);
	}
}

?>