<?php
/*****************************************************************************************
	文件： {phpok}/admin/tool_control.php
	备注： 工具箱的一些功能说明
	版本： 4.x;
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年2月28日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tool_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$this->view("tool_index");
	}
}

?>