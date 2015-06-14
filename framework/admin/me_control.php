<?php
/***********************************************************
	Filename: {phpok}/admin/me_control.php
	Note	: 管理员面板信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年04月24日 06时14分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class me_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function setting_f()
	{
		$rs = $this->model('admin')->get_one($_SESSION['admin_id'],'id');
		$this->assign('rs',$rs);
		$this->view('me_setting');
	}

	function submit_f()
	{
		$oldpass = $this->get("oldpass");
		if(!$oldpass)
		{
			error(P_Lang('管理员密码验证不能为空'),$this->url("me","setting"),"error");
		}
		$rs = $this->model('admin')->get_one($_SESSION["admin_id"]);
		if(!password_check($oldpass,$rs["pass"]))
		{
			error(P_Lang("管理员密码不正确"),$this->url("me","setting"),"error");
		}
		$name = $this->get('name');
		$array = array('email'=>$this->get('email'));
		$update_login = false;
		if($name && $name != $_SESSION['admin_account'])
		{
			//修改管理员账号
			$check = $this->model('admin')->check_account($name,$_SESSION['admin_id']);
			if($check)
			{
				error(P_Lang('管理员账号已经存在，请重新设置'),$this->url('me','setting'),'error');
			}
			$array['account'] = $name;
			$update_login = true;
		}
		$newpass = $this->get("newpass");
		if($newpass)
		{
			$chkpass = $this->get("chkpass");
			if($newpass != $chkpass)
			{
				error(P_Lang("两次输入的新密码不一致"),$this->url("me","password"),"error");
			}
			$array['pass'] = password_create($newpass);
			$_SESSION["admin_rs"]["pass"] = $array['pass'];
		}
		$this->model('admin')->save($array,$_SESSION['admin_id']);
		if($update_login)
		{
			error(P_Lang('您的信息已更新成功，请重新登录'),$this->url('logout'),'ok');
		}
		else
		{
			$html = '<input type="button" value=" '.P_Lang('确定').' " class="submit" onclick="$.dialog.close();" />';
			error_open(P_Lang('密码修改成功，请下次登录后使用新密码登录！'),"ok",$html);
		}
	}
}
?>