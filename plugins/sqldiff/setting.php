<?php
/***********************************************************
	Filename: plugins/sqldiff/setting.php
	Note	: 数据库比较配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年3月13日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_sqldiff extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function index()
	{
		//取得导航菜单
		$top_menu_list = $this->model('sysmenu')->get_list(0,1);
		$this->assign('top_menu_list',$top_menu_list);
		return $this->plugin_tpl('install.html');
	}

	function save()
	{
		$rs = $this->plugin_info();
		$ext = array();
		$ext['sysmenu_id'] = $rs['param']['sysmenu_id'];
		$ext['manage_title'] = $this->get('manage_title');
		$ext['root_id'] = $this->get('root_id');
		if(!$ext['manage_title']) $ext['manage_title'] = '数据库比较';
		//更新导航名称
		$this->model('sysmenu')->save(array('title'=>$ext['manage_title'],'parent_id'=>$ext['root_id'],'taxis'=>$rs['taxis']),$ext['sysmenu_id']);
		//更新内容
		$this->plugin_save($ext,$rs['id']);
	}

	//更新导航菜单状态
	function status()
	{
		$rs = $this->plugin_info();
		$this->model('sysmenu')->update_status($rs['param']['sysmenu_id'],$rs['status']);
	}
}
?>