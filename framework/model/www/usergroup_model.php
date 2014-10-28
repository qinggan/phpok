<?php
/*****************************************************************************************
	文件： {phpok}/model/www/usergroup_model.php
	备注： 用户组前端相应操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月6日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class usergroup_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function group_rs($uid)
	{
		$sql = "SELECT g.* FROM ".$this->db->prefix."user_group g ";
		$sql.= "JOIN ".$this->db->prefix."user u ON(g.id=u.group_id) WHERE u.id='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_default=1 AND status=1";
			$rs = $this->db->get_one($sql);
		}
		return $rs;
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

	//取得开放的会员组列表
	public function opened_grouplist($pri='')
	{
		$sql = "SELECT id,title,register_status,tbl_id,fields FROM ".$this->db->prefix."user_group ";
		$sql.= "WHERE status=1 AND (is_open=1 OR is_default=1) AND is_guest!=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}
}

?>