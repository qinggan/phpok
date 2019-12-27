<?php
/**
 * 会员退出接口
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
		$this->success();
	}
}