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

/**
 * 删除会员字段核心菜单
**/
$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='user' AND func='fields'";
$rs = $this->db->get_one($sql);
if($rs){
	$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid='".$rs['id']."'";
	$this->db->query($sql);
	$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$rs['id']."'";
	$this->db->query($sql);
}

/**
 * 增加一些字段用于完善模块
 * 2023年9月14日
**/
$fields = $this->db->list_fields('module');
if(!in_array('tbname',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."module ADD tbname varchar(50) NOT NULL COMMENT '表别名，仅限英文字母数字'";
	$this->db->query($sql);
}
$fields = $this->db->list_fields('fields');
if(!in_array('hidden',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."fields ADD hidden TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0显示1隐藏'";
	$this->db->query($sql);
}
if(!in_array('is_system',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."fields ADD is_system TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0常规1系统'";
	$this->db->query($sql);
}


/**
 * 2023年9月14日 更新结束
**/

/**
 * 项目增加图标字段
 * 2023年10月6日 
**/

$fields = $this->db->list_fields('project');
if(!in_array('icon',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."project ADD icon varchar(255) NOT NULL COMMENT '侧边栏文本图标'";
	$this->db->query($sql);
}

/**
 * 2023年10月10日 更新结束
**/