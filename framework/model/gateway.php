<?php
/*****************************************************************************************
	文件： {phpok}/model/gateway.php
	备注： 第三方网关接入管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月29日 23时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gateway_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all($status=0)
	{
		$grouplist = $this->group_all();
		foreach($grouplist as $key=>$value){
			$grouplist[$key] = array('title'=>$value);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway ";
		if($status){
			$sql .= " WHERE status='".($status == 1 ? 1 : 0)."' ";
		}
		$sql.= " ORDER BY taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$grouplist[$value['type']]['list'][] = $value;
			}
		}
		return $grouplist;
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	public function get_default($type)
	{
		if(!$type){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE type='".$type."' AND site_id='".$this->site_id."' AND status=1";
		$sql.= " AND is_default=1 ORDER BY id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

}

?>