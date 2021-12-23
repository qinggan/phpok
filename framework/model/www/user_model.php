<?php
/***********************************************************
	Filename: {$phpok}/model/user.php
	Note	: 用户模块
	Version : 3.0
	Author  : qinggan
	Update  : 2013年5月4日
***********************************************************/
class user_model extends user_model_base
{
	var $psize = 20;
	function __construct()
	{
		parent::__construct();
	}

	//取得全部用户ID
	function get_all_from_uid($uid,$pri="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$uid.")";
		return $this->db->get_all($sql,$pri);
	}

	function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	//更新用户头像
	function update_avatar($file,$uid)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET avatar='".$file."' WHERE id='".$uid."'";
		return $this->db->query($sql);
	}
}
?>