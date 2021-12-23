<?php
/**
 * 安装文件_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
phpok_loadsql($this->db,$this->dir_app.'weixin/install.sql',true);

//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'微信应用','status'=>1);
$menu['appfile'] = 'weixin';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = '';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'配置','identifier'=>'config','taxis'=>20);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'用户','identifier'=>'user','taxis'=>30);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>40);
	$this->model('popedom')->save($tmparray);
}
