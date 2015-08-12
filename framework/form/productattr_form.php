<?php
/*****************************************************************************************
	文件： {phpok}/form/productattr_form.php
	备注： 产品属性编辑器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月07日 09时08分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class productattr_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/productattr_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		//
	}

	public function phpok_get($rs,$appid="admin")
	{
		//
	}

	public function phpok_show($rs,$appid="admin")
	{
		//
	}
}
?>