<?php
/**
 * 单独运行升级文件
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 6.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2023年3月3日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 变更fields的扩展字段存储方式
**/
$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."fields_ext` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',`fields_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展字段ID',`keyname` varchar(255) NOT NULL COMMENT '键名',`keydata` text NOT NULL COMMENT '键值',PRIMARY KEY (`id`),KEY `fields_id` (`fields_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='字段扩展表' AUTO_INCREMENT=1";
$this->db->query($sql);

$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ext!=''";
$tmplist = $this->db->get_all($sql);
if($tmplist){
	foreach($tmplist as $key=>$value){
		$xmldata = unserialize($value['ext']);
		$file = $this->dir_data.'xml/fields_'.$value['id'].'.xml';
		$is_delete = false;
		if(file_exists($file)){
			$tmp = $this->lib('xml')->read($file);
			if($tmp){
				$xmldata = array_merge($xmldata,$tmp);
			}
			$is_delete = true;
		}
		$sql = "DELETE FROM ".$this->db->prefix."fields_ext WHERE fields_id='".$value['id']."'";
		$this->db->query($sql);
		foreach($xmldata as $k=>$v){
			if($v && is_array($v)){
				$v = serialize($v);
			}
			$array = array('fields_id'=>$value['id'],'keyname'=>$k,'keydata'=>$v);
			$this->db->insert($array,'fields_ext');
		}
		//删除文件
		if($is_delete){
			$this->lib('file')->rm($file);
		}
	}
	$sql = "UPDATE ".$this->db->prefix."fields SET ext=''";
	$this->db->query($sql);
	exit('ok');
}
exit('end');