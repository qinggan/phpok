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
	function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//取得单个配置参数
	public function one($id,$siteid=0,$type='identifier')
	{
		if(!$id)
		{
			return false;
		}
		$all = $this->all($siteid);
		if(!$all)
		{
			return false;
		}
		$rs = false;
		foreach($all as $key=>$value)
		{
			if($value[$type] == $id)
			{
				$rs = $value;
				break;
			}
		}
		return $rs;
	}

	//取得全部
	public function all($siteid=0)
	{
		$siteid = intval($siteid);
		$siteid = $siteid ? $siteid.',0' : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id IN(".$siteid.") AND status=1";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		foreach($rslist as $key=>$value)
		{
			if($value['ext'])
			{
				$ext = unserialize($value['ext']);
				unset($value['ext']);
				$value = array_merge($value,$ext);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}

?>