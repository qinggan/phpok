<?php
/***********************************************************
	Filename: phpok/admin/login_control.php
	Note	: 管理员登录模块
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-19 13:13
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("admin");
		$this->model("site");
	}

	# 登录页面
	function index_f()
	{
		if($_SESSION['admin_id'])
		{
			error(P_Lang('您已成功登录'),$this->url('index'),'ok');
		}
		$vcode = $this->config["is_vcode"] && function_exists("imagecreate") ? true : false;
		$this->assign("vcode",$vcode);
		$login = $this->config['admin_tpl_login'] ? $this->config['admin_tpl_login'] : 'login';
		if(!$this->tpl->check_exists($login))
		{
			$login = 'login';
		}
		//读取语言包
		$langlist = $this->lib('xml')->read($this->dir_root.'data/xml/langs.xml');
		$this->assign('langlist',$langlist);
		//判断默认语言
		$langid = $this->get('langid');
		if(!$langid)
		{
			$langid = isset($_SESSION['admin_lang_id']) ? $_SESSION['admin_lang_id'] : 'cn';
		}
		$_SESSION['admin_lang_id'] = $langid;
		$this->assign('langid',$langid);
		$GLOBALS['app']->language($langid);
		$this->view($login);
	}

	//验证登录
	function check_f()
	{
		if($_SESSION['admin_id'])
		{
			$this->json(P_Lang('您已成功登录，无需再次验证'));
		}
		$user = $this->get('user');
		if(!$user)
		{
			$this->json(P_Lang('管理员账号不能为空'));
		}
		$pass = $this->get('pass');
		if(!$pass)
		{
			$this->json(P_Lang('密码不能为空'));
		}
		//验证码检测
		if($this->config['is_vcode'] && function_exists('imagecreate'))
		{
			$code = $this->get("_code");
			if(!$code)
			{
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode_admin'])
			{
				$this->json(P_Lang('验证码填写不正确'));
			}
		}
		$rs = $this->model('admin')->get_one_from_name($user);
		if(!$rs)
		{
			$this->json(P_Lang('管理员信息不存在'));
		}
		if(!password_check($pass,$rs["pass"]))
		{
			$this->json(P_Lang('管理员密码输入不正确'));
		}
		if(!$rs["status"])
		{
			$this->json(P_Lang("管理员账号已被锁定，请联系超管！"));
		}
		//获取管理员的权限
		if(!$rs["if_system"])
		{
			$popedom_list = $this->model('admin')->get_popedom_list($rs["id"]);
			if(!$popedom_list)
			{
				$this->json(P_Lang('你的管理权限未设置好，请联系超级管理员进行设置'));
			}
			$_SESSION["admin_popedom"] = $popedom_list;
		}
		$_SESSION["admin_id"] = $rs["id"];
		$_SESSION["admin_account"] = $rs["account"];
		$_SESSION["admin_rs"] = $rs;
		$_SESSION["admin_site_id"] = $this->site['id'];
		unset($_SESSION['vcode_admin']);
		$this->json(true);
	}
}
?>