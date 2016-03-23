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

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
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