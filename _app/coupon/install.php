<?php
/**
 * 安装文件_适用于整个PHPOK5平台的优惠系统
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年01月02日 15时35分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

//安装SQL
phpok_loadsql($this->db,$this->dir_app.'coupon/install.sql',true);

//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'优惠平台','status'=>1);
$menu['appfile'] = 'coupon';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'newtab';
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
	$tmparray = array('gid'=>$insert_id,'title'=>'批处理','identifier'=>'plaction','taxis'=>50);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>60);
	$this->model('popedom')->save($tmparray);
}
