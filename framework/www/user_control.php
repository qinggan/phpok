<?php
/**
 * 用户详细页，开放浏览
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年07月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$uid = $this->get("uid");
		$id = $this->get('id','int');
		if(!$uid && !$id){
			$this->error(P_Lang('未指定用户信息'));
		}
		if(!$uid){
			$uid = $id;
		}
		//用户信息
		$user_rs = $this->model('user')->get_one($uid);
		if(!$user_rs || !$user_rs['status'] || $user_rs['status'] == 2){
			$this->error(P_Lang('用户信息不存在或禁止查看'));
		}
		$this->assign("user_rs",$user_rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'user-index';
		}
		$this->view($tplfile);
	}
}