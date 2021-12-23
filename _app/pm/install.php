<?php
/**
 * 安装文件_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
phpok_loadsql($this->db,$this->dir_app.'pm/install.sql',true);
//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'站内短消息','status'=>1);
$menu['appfile'] = 'pm';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'newtab';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'查看','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'发布','identifier'=>'add','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'删除','identifier'=>'delete','taxis'=>10);
	$this->model('popedom')->save($tmparray);
}
