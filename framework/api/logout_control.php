<?php
/**
 * 用户退出接口
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月27日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
	}

	/**
	 * 退出
	**/
	public function index_f()
	{
		$this->session->unassign('user_id');
		$this->session->unassign('user_gid');
		$this->session->unassign('user_name');
		$this->session->unassign('user_status');
		$this->success();
	}
}