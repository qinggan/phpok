<?php
/**
 * 会员详细页，开放浏览
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年07月01日
**/

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
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'user_info';
		}
		$this->view($tplfile);
	}
}