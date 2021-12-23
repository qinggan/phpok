<?php
/**
 * 接口应用_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
namespace phpok\app\control\social;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//$info = "";
		//$this->error($info);
		$this->success();
	}

	public function homepage_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$data = array();
		$data['banner'] = $this->get('banner');
		$data['mbanner'] = $this->get('mbanner');
		$data['heart'] = $this->get('heart');
		$data['tags'] = $this->get('tags');
		$this->model('social')->homepage($this->session->val('user_id'),$data);
		$this->success();
	}

	public function idol_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$type = $this->get('type');
		if($type == 'add'){
			$act = $this->model('social')->idol_add($this->session->val('user_id'),$id);
			if(!$act){
				$this->error(P_Lang('添加关注失败，请联系客服'));
			}
			$this->success();
		}
		$act = $this->model('social')->idol_del($this->session->val('user_id'),$id);
		if(!$act){
			$this->error(P_Lang('取消关注失败，请联系客服'));
		}
		$this->success();
	}

	public function black_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$type = $this->get('type');
		if($type == 'add'){
			$act = $this->model('social')->black_add($this->session->val('user_id'),$id);
			if(!$act){
				$this->error(P_Lang('加入黑名单失败，请联系客服'));
			}
			$this->success();
		}
		$act = $this->model('social')->black_del($this->session->val('user_id'),$id);
		if(!$act){
			$this->error(P_Lang('取消黑名单失败，请联系客服'));
		}
		$this->success();
	}
}
