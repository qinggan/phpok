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
class usergroup_model extends usergroup_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//取得会员组ID
	//uid，会员ID
	function group_id($uid=0)
	{
		$grouplist = $this->all();
		if(!$grouplist)
		{
			return false;
		}
		$groupid = 0;
		if($uid)
		{
			$sql = "SELECT group_id FROM ".$this->db->prefix."user WHERE id='".$uid."'";
			$tmp = $this->db->get_one($sql);
			if($tmp && $tmp['group_id'])
			{
				$groupid = $tmp['group_id'];
			}
			if(!$groupid)
			{
				$tmp = false;
				foreach($grouplist as $key=>$value)
				{
					if($value['is_default'] && !$value['is_guest'])
					{
						$tmp = $value;
						break;
					}
				}
				if(!$tmp || !$tmp['id'])
				{
					return false;
					
				}
				$groupid = $tmp['id'];
			}
		}
		else
		{
			$tmp = false;
			foreach($grouplist as $key=>$value)
			{
				if($value['is_guest'])
				{
					$tmp = $value;
					break;
				}
			}
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
		return $groupid;
	}

	//通过会员取得会员组信息
	function group_rs($uid=0)
	{
		$gid = $this->group_id($uid);
		if(!$gid)
		{
			return false;
		}
		return $this->one($gid);
	}

	function get_default($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_default=1 AND status=1";
		return $this->db->get_one($sql);
	}

	//读取单个会员组信息
	function one($id)
	{
		$rslist = $this->all();
		if(!$rslist)
		{
			return false;
		}
		$rs = false;
		foreach($rslist as $key=>$value)
		{
			if($value['id'] == $id)
			{
				$rs = $value;
				break;
			}
		}
		return $rs;
	}

	function all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE status=1";
		return $this->db->get_all($sql);
	}
}
?>