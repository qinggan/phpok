<?php
/**
 * 卸载文件_用于管理多语言，支持批量翻译等操作
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
//phpok_loadsql($this->db,$this->dir_app.'multi_language/uninstall.sql',true);
$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='multi_language'";
$rs = $this->db->get_one($sql);
if($rs){
	$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid='".$rs['id']."'";
	$this->db->query($sql);
	$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$rs['id']."'";
	$this->db->query($sql);
}
