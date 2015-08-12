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
		if(!$uid){
			error(P_Lang('未指定会员信息'));
		}
		$user_rs = $this->model('user')->get_one($uid);
		$this->assign("user_rs",$user_rs);
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
		return $this->db->get_one($sql);
	}
}
?>