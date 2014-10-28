<?php
/*****************************************************************************************
	文件： {phpok}/model/api/usergroup_model.php
	备注： API接口下的会员Model处理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月24日 11时11分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class usergroup_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得会员组ID
	function group_id($uid=0)
	{
		$groupid = 0;
		if($uid)
		{
			$sql = "SELECT group_id FROM ".$this->db->prefix."user WHERE id='".$uid."'";
			$tmp = $this->db->get_one($sql);
			if($tmp && $tmp['group_id']) $groupid = $tmp['group_id'];
			if(!$groupid)
			{
				$sql = "SELECT id FROM ".$this->db->prefix."user_group WHERE status=1 AND is_default=1 ORDER BY id ASC LIMIT 1";
				$tmp = $this->db->get_one($sql);
				if(!$tmp || !$tmp['id'])
				{
					return false;
				}
				$groupid = $tmp['id'];
			}
		}
		else
		{
			$sql = "SELECT id FROM ".$this->db->prefix."user_group WHERE status=1 AND is_guest=1 ORDER BY id ASC LIMIT 1";
			$tmp = $this->db->get_one($sql);
			if(!$tmp || !$tmp['id'])
			{
				return false;
			}
			$groupid = $tmp['id'];
		}
		if(!$groupid)
		{
			return false;
		}

		$sql = "SELECT id FROM ".$this->db->prefix."user_group WHERE status=1 AND id='".$groupid."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp || !$tmp['id'])
		{
			return false;
		}
		return $groupid;
	}
}
?>