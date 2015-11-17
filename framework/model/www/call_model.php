<?php
/*****************************************************************************************
	文件： {phpok}/model/www/call_model.php
	备注： 数据调用中心参数配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年11月05日 13时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model extends call_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

}

?>