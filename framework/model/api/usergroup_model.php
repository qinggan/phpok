<?php
/*****************************************************************************************
	文件： {phpok}/model/api/usergroup_model.php
	备注： API接口下的用户Model处理
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

	//通过用户取得用户组信息
	public function group_rs($uid=0)
	{
		$gid = $this->group_id($uid);
		if(!$gid)
		{
			return false;
		}
		return $this->one($gid);
	}

	public function get_default($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_default=1 AND status=1";
		return $this->db->get_one($sql);
	}

	//读取单个用户组信息
	public function one($id)
	{
		$rslist = $this->all();
		if(!$rslist)
		{
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			if($value['id'] == $id){
				$rs = $value;
				break;
			}
		}
		if(!$rs || count($rs)<1){
			return false;
		}
		return $rs;
	}

	public function all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE status=1";
		return $this->db->get_all($sql);
	}
}