<?php
/*****************************************************************************************
	文件： task/clear.php
	备注： 清空缓存
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月11日 11时35分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$this->lib('file')->rm($this->dir_root."data/tpl_www/");
$this->lib('file')->rm($this->dir_root."data/tpl_admin/");
$this->lib('file')->rm($this->dir_root."data/tpl_html/");
$this->db->cache_clear();
return true;
?>