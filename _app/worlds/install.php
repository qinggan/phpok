<?php
/**
 * 安装文件_管理全球国家及州/省信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年05月27日 19时51分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
phpok_loadsql($this->db,$this->dir_app.'worlds/install.sql',true);
//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'国家管理','status'=>1);
$menu['appfile'] = 'worlds';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = '';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'添加','identifier'=>'add','taxis'=>20);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'修改','identifier'=>'modify','taxis'=>30);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'状态','identifier'=>'status','taxis'=>40);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>50);
	$this->model('popedom')->save($tmparray);
}
