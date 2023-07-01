<?php
/**
 * 管理员退出
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年05月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$name = $this->session->val('admin_account');
		$this->session->unassign('admin_id');
		$this->session->unassign('admin_account');
		$this->session->unassign('admin_rs');
		$this->session->unassign('adm_develop');
		$this->session->unassign('admin_login_time');
		$this->session->unassign('admin_long_time');
		$this->success(P_Lang('管理员{admin_name}成功退出',array('admin_name'=>' <span class="red">'.$name.'</span> ')),$this->url('login'));
	}
}