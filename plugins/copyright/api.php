<?php
/***********************************************************
	Filename: plugins/copyright/api.php
	Note	: 插件API响应
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年2月1日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_copyright extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function domain()
	{
		$rs = $this->plugin_info();
		$domain = $this->get('domain');
		if(!$domain) $this->json('域名不能为空');
		$tid = $this->get('tid','int');
		$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE domain='".$domain."'";
		if($tid)
		{
			$sql .= " AND id!='".$tid."'";
		}
		$rs = $this->db->get_one($sql);
		if($rs) $this->json('域名已存在');
		$this->json(true);
	}
}
?>