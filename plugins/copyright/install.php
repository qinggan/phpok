<?php
/***********************************************************
	Filename: plugins/copyright/install.php
	Note	: 授权安装文档
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月31日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_copyright extends phpok_plugin
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
		if(!$ext['manage_title']) $ext['manage_title'] = '授权管理';
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
		//导入Table
		$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."copyright` (`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',  `domain` varchar(100) NOT NULL COMMENT '域名', `code` varchar(50) NOT NULL COMMENT '认证码', `regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间', `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1生效0未生效', `fullname` varchar(100) NOT NULL COMMENT '注册人姓名', `note` varchar(255) NOT NULL COMMENT '备注', `version` varchar(30) NOT NULL COMMENT 'PHPOK版本', `email` varchar(100) NOT NULL COMMENT '邮箱', `phone` varchar(100) NOT NULL COMMENT '电话或手机号', `im` varchar(255) NOT NULL COMMENT 'IM工具', `md5file` varchar(50) NOT NULL COMMENT 'license.php文件的MD5值',`type` ENUM('LGPL','PBIZ','CBIZ') NOT NULL DEFAULT 'LGPL' COMMENT '授权类型', PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='域名授权认证中心' AUTO_INCREMENT=1";
		$this->db->query($sql);
	}
}
?>