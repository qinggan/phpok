<?php
/***********************************************************
	Filename: {phpok}/model/address.php
	Note	: 地址库管理，每个会员都有30个地址库
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月27日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得指定会员的地址库列表
	//user_id，会员id
	//type，类型，仅支持shipping和billing两种
	function address_list($user_id,$type='shipping')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."address WHERE user_id=".$user_id." AND type_id='".$type."' ORDER BY id DESC LIMIT 30";
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储更新地址库信息
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"address",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"address");
		}
	}
}