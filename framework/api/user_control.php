<?php
/**
 * 用户接口
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
	 * 查看用户基本信息
	**/
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
		$data = array();
		$user_rs = $this->model('user')->get_one($uid);
		if(!$user_rs || !$user_rs['status'] || $user_rs['status'] == 2){
			$this->error(P_Lang('用户不存在或不被显示'));
		}
		$data['user'] = array('id'=>$user_rs['id'],'user'=>$user_rs['user'],'avatar'=>$user_rs['avatar'],'regtime'=>$user_rs['regtime']);
		if($this->session->val('user_id')){
			unset($user_rs['pass'],$user_rs['email'],$user_rs['mobile'],$user_rs['code']);
			$data['user'] = $user_rs;
		}
		$this->success($data);
	}
}