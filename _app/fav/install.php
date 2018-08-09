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

$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."fav` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',`user_id` int(10) unsigned NOT NULL COMMENT '会员ID',`thumb` varchar(255) NOT NULL COMMENT '缩略图',`title` varchar(255) NOT NULL COMMENT '标题',`note` varchar(255) NOT NULL COMMENT '摘要',`addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',`lid` int(11) NOT NULL COMMENT '关联主题',PRIMARY KEY (`id`),KEY `user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员收藏夹' AUTO_INCREMENT=1";

$this->db->query($sql);

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