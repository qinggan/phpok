<?php
/**
 * 安装文件_管理整个平台的文件，包括修改自身，仅限系统管理员
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年04月09日 15时31分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
//phpok_loadsql($this->db,$this->dir_app.'filemanage/install.sql',true);
//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'文件管理','status'=>1);
$menu['appfile'] = 'filemanage';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'newtab';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'添加','identifier'=>'add','taxis'=>20);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'编辑','identifier'=>'edit','taxis'=>30);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'上传','identifier'=>'upload','taxis'=>40);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>50);
	$this->model('popedom')->save($tmparray);
}
