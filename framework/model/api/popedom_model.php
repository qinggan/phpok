<?php
/*****************************************************************************************
	文件： {phpok}/model/api/popedom_model.php
	备注： API接口读取权限
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月24日 11时09分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model extends popedom_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//设置站点ID
	public function siteid($siteid)
	{
		$this->site_id = $siteid;
	}

	//取得权限返回值1或0
	public function val($pid,$groupid,$type='post1')
	{
		$popedom = $this->_popedom_list($groupid);
		if(!$popedom)
		{
			return false;
		}
		if(in_array($type.':'.$pid,$popedom))
		{
			return '1';
		}
		return '0';
	}
}