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
	function __construct()
	{
		parent::__construct();
	}

	//邮箱登录
	function user_email($email,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}

	//手机登录
	public function user_mobile($mobile,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE mobile='".$mobile."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}

	//更新用户密码
	function update_password($pass,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET pass='".$pass."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//短信更新用户密码
	function update_smspass($pass,$mobile)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET pass='".$pass."' WHERE mobile='".$mobile."'";
		return $this->db->query($sql);
	}

	//更新用户手机
	function update_mobile($mobile,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET mobile='".$mobile."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//更新用户邮箱
	function update_email($email,$id=0,$chk=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET email='".$email."',email_chk='".$chk."' WHERE id='".$id."'";
		return $this->db->query($sql);
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

	function uid_from_mobile($mobile,$id="")
	{
		if(!$mobile) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE mobile='".$mobile."'";
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

	//更新用户头像
	function update_avatar($file,$uid)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET avatar='".$file."' WHERE id='".$uid."'";
		return $this->db->query($sql);
	}

	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}