<?php
/**
 * 安装文件_用于管理多语言，支持批量翻译等操作
 * @作者 phpok.com <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年10月13日 18时20分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
//phpok_loadsql($this->db,$this->dir_app.'multi_language/install.sql',true);
//增加导航菜单
$menu = array('parent_id'=>5,'title'=>'多语言管理','status'=>1);
$menu['appfile'] = 'multi_language';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'globe';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmpdata = array();
	$tmpdata['list'] = '查看';
	$tmpdata['add'] = '添加';
	$tmpdata['modify'] = '修改';
	$tmpdata['status'] = '审核';
	$tmpdata['delete'] = '删除';
	$i=0;
	foreach($tmpdata as $key=>$value){
		$tmp = array();
		$tmp['gid'] = $insert_id;
		$tmp['title'] = $value;
		$tmp['identifier'] = $key;
		$tmp['taxis'] = ($i+1)*10;
		$this->model('popedom')->save($tmp);
		$i++;
	}
}
