<?php
/***********************************************************
	Filename: {phpok}/www/login_control.php
	Note	: 会员登录操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年07月01日 05时33分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back) $_back = $this->url;
		if($_SESSION["user_id"])
		{
			error($this->lang[$this->app_id][1001],$_back);
		}
		if(!$this->site['login_status'])
		{
			$tips = $this->site["login_close"] ? $this->site["login_close"] : $this->lang[$this->app_id][1002];
			error($tips,$_back,'error',10);
		}
		//随机数，防止恶意注册刷新代码
		//if(!$_SESSION['login_spam_code'])
		//{
			$_SESSION['login_spam_code'] = str_rand(10);
		//}
		$this->assign("_back",$_back);
		$this->view("login");
	}

	function getpass_f()
	{
		$_SESSION['getpass_spam_code'] = str_rand(10);
		$this->view("login_getpass");
	}

	function repass_f()
	{
		$_SESSION['repass_spam_code'] = str_rand(10);
		$code = $this->get('_code');
		if(!$code)
		{
			error($this->lang[$this->app_id][1004],'','error');
		}
		$time = intval(substr($code,-10));
		if(($this->time - $time) > (24*60*60))
		{
			error($this->lang[$this->app_id][1005],$this->url('login','getpass'),'error',10);
		}
		$uid = $this->model('user')->uid_from_chkcode($code);
		if(!$uid)
		{
			error($this->lang[$this->app_id][1006],$this->url('login','getpass'),'error',10);
		}
		$user = $this->model('user')->get_one($uid);
		$this->assign("user",$user);
		$this->assign('code',$code);
		$this->view('login_repass');
	}

}
?>