<?php
/**
 * 收藏夹安装
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月01日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

phpok_loadsql($this->db,$this->dir_app.'fav/install.sql',true);

//增加导航菜单
$menu = array('parent_id'=>5,'title'=>P_Lang('收藏夹管理'),'status'=>1);
$menu['appfile'] = 'fav';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'newtab';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>10);
	$this->model('popedom')->save($tmparray);
}