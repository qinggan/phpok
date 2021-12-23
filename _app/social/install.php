<?php
/**
 * 安装文件_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
phpok_loadsql($this->db,$this->dir_app.'social/install.sql',true);
//增加导航菜单
//$menu = array('parent_id'=>5,'title'=>'社交服务','status'=>1);
//$menu['appfile'] = 'social';
//$menu['taxis'] = 255;
//$menu['site_id'] = 0;
//$menu['icon'] = 'newtab';
//$insert_id = $this->model('sysmenu')->save($menu);
//if($insert_id){
//	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
//	$this->model('popedom')->save($tmparray);
//	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>10);
//	$this->model('popedom')->save($tmparray);
//}
