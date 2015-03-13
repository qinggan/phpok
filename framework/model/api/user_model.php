<?php
/***********************************************************
	Filename: {$phpok}/model/user.php
	Note	: 会员模块
	Version : 3.0
	Author  : qinggan
	Update  : 2013年5月4日
***********************************************************/
class user_model extends user_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//邮箱登录
	function user_email($email)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."'";
		return $this->db->get_one($sql);
	}

	//更新会员验证串
	function update_code($code,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET code='".$code."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//更新会员密码
	function update_password($pass,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET pass='".$pass."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得全部会员ID
	function get_all_from_uid($uid,$pri="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$uid.")";
		return $this->db->get_all($sql,$pri);
	}

	function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	function uid_from_email($email,$id="")
	{
		if(!$email) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($id)
		{
			$sql.= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	function uid_from_chkcode($code)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE code='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	//更新会员头像
	function update_avatar($file,$uid)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET avatar='".$file."' WHERE id='".$uid."'";
		return $this->db->query($sql);
	}

	//更新会员登录操作
	public function update_session($uid)
	{
		$rs = $this->get_one($uid);
		if(!$rs || $rs['status'] != 1){
			return false;
		}
		$_SESSION["user_id"] = $uid;
		$_SESSION["user_rs"] = $rs;
		$_SESSION["user_name"] = $rs["user"];
		return true;
	}

	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>