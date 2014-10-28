<?php
/***********************************************************
	Filename: plugins/copyright/www.php
	Note	: 前端插件授权查询
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年2月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class www_copyright extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function info()
	{
		$domain = $this->get('domain');
		if(!$domain) error('未指定域名或注册码','','error');
		$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE domain='".$domain."' OR code='".$domain."' AND status=1";
		$rs = $this->db->get_one($sql);
		$this->assign('rs',$rs);
		echo $this->plugin_tpl('info.html');
	}
}
?>