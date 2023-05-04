<?php
/**
 * 升级运行文件
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年12月28日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 增加云市场客户端
**/
$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."yunmarket_client` (`id` int(10) unsigned NOT NULL COMMENT '主键ID',`md5` varchar(255) NOT NULL COMMENT 'MD5码',`version` varchar(50) NOT NULL COMMENT '版本号',`version_update` varchar(50) NOT NULL COMMENT '内部版本号',`folder` varchar(50) NOT NULL COMMENT '安装目录，用于删操作',`dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='验证是否已安装'";
$this->db->query($sql);


/**
 * 安装云市场
**/
$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='yunmarket'";
$rs = $this->db->get_one($sql);
if(!$rs){
	$menu = array('parent_id'=>5,'title'=>'云市场','status'=>1);
	$menu['appfile'] = 'yunmarket';
	$menu['taxis'] = 130;
	$menu['site_id'] = 0;
	$menu['icon'] = 'windows8';
	$menu['if_system'] = 1;
	$insert_id = $this->model('sysmenu')->save($menu);
	if($insert_id){
		$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'配置','identifier'=>'setting','taxis'=>20);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'安装','identifier'=>'install','taxis'=>30);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'卸载','identifier'=>'uninstall','taxis'=>40);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'升级','identifier'=>'update','taxis'=>50);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'状态','identifier'=>'status','taxis'=>60);
		$this->model('popedom')->save($tmparray);
		$tmparray = array('gid'=>$insert_id,'title'=>'备份','identifier'=>'backup','taxis'=>70);
		$this->model('popedom')->save($tmparray);
	}
}

