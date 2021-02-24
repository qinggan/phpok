<?php
/**
 * 会员接口
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年9月3日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 查看会员基本信息
	**/
	public function index_f()
	{
		$uid = $this->get("uid");
		if(!$uid){
			$this->error(P_Lang('未指定会员信息'));
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('游客无法查看会员信息'));
		}
		$user_rs = $this->model('user')->get_one($uid);
		unset($user_rs['pass'],$user_rs['email'],$user_rs['mobile'],$user_rs['code']);
		$this->success($user_rs);
	}
}