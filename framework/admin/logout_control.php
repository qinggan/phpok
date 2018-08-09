<?php
/**
 * 管理员退出
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
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
		$this->success(P_Lang('管理员{admin_name}成功退出',array('admin_name'=>' <span class="red">'.$name.'</span> ')),$this->url('login'));
	}
}