<?php
/**
 * 升级运行文件
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年12月28日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 删除无用的 safecheck
 * 时间：2021年12月28日
**/
$folder = $this->dir_app."safecheck";
if(file_exists($folder)){
	$this->lib('file')->rm($this->dir_app."safecheck",true);
}
$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='safecheck'";
$rs = $this->db->get_one($sql);
if($rs){
	$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid='".$rs['id']."'";
	$this->db->query($sql);
	$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$rs['id']."'";
	$this->db->query($sql);
}

/**
 * 检查财富表字段
**/
$fields = $this->db->list_fields('wealth');
if(!in_array('banner',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."wealth ADD banner varchar(255) NOT NULL COMMENT '大图'";
	$this->db->query($sql);
}
if(!in_array('thumb',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."wealth ADD thumb varchar(255) NOT NULL COMMENT '小图'";
	$this->db->query($sql);
}
if(!in_array('iconfont',$fields)){
	$sql = "ALTER TABLE ".$this->db->prefix."wealth ADD iconfont varchar(255) NOT NULL COMMENT '字体图标'";
	$this->db->query($sql);
}

/**
 * 删除 user_autologin
 * 时间：2022年1月10日
**/
$tblist = $this->db->list_tables();
if(in_array($this->db->prefix."user_autologin",$tblist)){
	$sql = "DROP TABLE IF EXISTS ".$this->db->prefix."user_autologin";
	$this->db->query($sql);
}

