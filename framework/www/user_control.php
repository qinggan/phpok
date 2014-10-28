<?php
/***********************************************************
	Filename: {phpok}www/user_control.php
	Note	: 会员趣事
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$uid = $this->get("uid");
		if(!$uid)
		{
			error("未指定会员信息");
		}
		if($uid == $_SESSION["user_id"])
		{
			header("Location:".$this->url."usercp/list.html?id=article");
			exit;
		}
		$user_rs = $this->model('user')->get_one($uid);
		$this->assign("user_rs",$user_rs);
		$is_atten = $this->check_atten($_SESSION["user_id"],$user_rs['user']);
		$this->assign("atten",$is_atten);
		$this->view("user_info");
	}

	//uid，关注的人
	//user，被关注的账号
	function check_atten($uid,$user)
	{
		if(!$uid || !$user) return false;
		$mid = "25";
		$sql = "SELECT l.id FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$mid." ext WHERE ext.post_uid='".$uid."' ";
		$sql.= "AND l.title='".$user."' AND l.status=1 LIMIT 1";
		//echo $sql;
		return $this->db->get_one($sql);
	}
}
?>