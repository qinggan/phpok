<?php
/*****************************************************************************************
	文件： {phpok}/model/www/payment_model.php
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

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	function get_all($site_id=0,$status=0,$mobile=0)
	{
		$condition = $site_id ? "site_id IN(0,".$site_id.")" : "site_id=0";
		$sql = "SELECT * FROM ".$this->db->prefix."payment_group WHERE ".$condition." ";
		$sql.= "AND status=1 ";
		$sql.= $mobile ? "AND is_wap=1 " : "AND is_wap=0 ";
		$sql.= "ORDER BY is_default DESC,taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist)
		{
			return false;
		}
		$ids = array_keys($rslist);
		$condition = "status=1 AND gid IN(".implode(",",$ids).") ";
		$condition.= $mobile ? "AND wap=1 " : "AND wap=0 ";
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE ".$condition." ORDER BY taxis ASC,id DESC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		foreach($tmplist AS $key=>$value){
			$rslist[$value['gid']]['paylist'][$value['id']] = $value;
		}
		return $rslist;
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['param'] && is_string($rs['param'])){
			$rs['param'] = unserialize($rs['param']);
		}
		//货币类型
		if($rs['currency']){
			$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE code='".$rs['currency']."'";
			$tmp = $this->db->get_one($sql);
			if($tmp){
				$rs['currency'] = $tmp;
			}else{
				unset($rs['currency']);
			}
		}
		return $rs;
	}
}

?>