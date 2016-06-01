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

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//设置站点ID
	public function siteid($siteid)
	{
		$this->site_id = $siteid;
	}

	//判断是否有阅读权限
	//pid，为项目ID
	//groupid，为会员组ID
	public function check($pid,$groupid=0,$type='read')
	{
		$popedom = $this->_popedom_list($groupid);
		if(!$popedom)
		{
			return false;
		}
		if(in_array($type.':'.$pid,$popedom))
		{
			return true;
		}
		return false;
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

	private function _popedom_list($groupid)
	{
		$sql = "SELECT popedom FROM ".$this->db->prefix."user_group WHERE id='".$groupid."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['popedom'])
		{
			return false;
		}
		$popedom = unserialize($rs['popedom']);
		if(!$popedom[$this->site_id])
		{
			return false;
		}
		return explode(",",$popedom[$this->site_id]);
	}
}

?>