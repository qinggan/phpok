<?php
/*****************************************************************************************
	文件： plugins/sqldiff/install.php
	备注： 数据库比较工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年3月13日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_sqldiff extends phpok_plugin
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
		$id = $this->plugin_id();
		$ext = array();
		$ext['manage_title'] = $this->get('manage_title');
		$ext['root_id'] = $this->get('root_id');
		if(!$ext['manage_title']) $ext['manage_title'] = '数据库比较';
		//增加到左侧菜单管理
		$array = array('title'=>$ext['manage_title'],'parent_id'=>$ext['root_id']);
		$array['status'] = 0;
		$array['appfile'] = 'plugin';
		$array['taxis'] = $this->get("taxis",'int');
		$array['func'] = 'exec';
		$array['ext'] = 'id='.$id.'&exec=manage';
		$array['if_system'] = 0;
		$array['site_id'] = $_SESSION['admin_site_id'];
		$insert_id = $this->model('sysmenu')->save($array);
		$ext['sysmenu_id'] = $insert_id;
		$this->plugin_save($ext,$id);
	}
}
?>