<?php
/*****************************************************************************************
	文件： {phpok}/model/api/payment_model.php
	备注： 支付信息
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月2日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_model extends payment_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if($rs['param'])
		{
			$rs['param'] = unserialize($rs['param']);
		}
		//货币类型
		if($rs['currency'])
		{
			$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE code='".$rs['currency']."'";
			$tmp = $this->db->get_one($sql);
			if($tmp)
			{
				$rs['currency'] = $tmp;
			}
			else
			{
				unset($rs['currency']);
			}
		}
		return $rs;
	}
}

?>