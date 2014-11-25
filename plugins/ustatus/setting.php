<?php
/*****************************************************************************************
	文件： ustatus/setting.php
	备注： 一键通知会员已通过审核
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年11月25日 09时15分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_ustatus extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	//
	function index()
	{
		return $this->plugin_tpl('setting.html');
	}

	//存储扩展表单内容
	function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['title'] = $this->get('email_title');
		$ext['content'] = $this->get('email_content','html');
		$ext['root_id'] = $this->get('root_id');
		$this->plugin_save($ext,$id);
	}
}

?>