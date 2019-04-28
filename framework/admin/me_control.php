<?php
/**
 * 管理员面板信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年03月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class me_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 个人信息设置页
	**/
	public function setting_f()
	{
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->assign('rs',$rs);
		$this->view('me_setting');
	}

	/**
	 * 修改密码弹出页
	**/
	public function pass_f()
	{
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->assign('rs',$rs);
		$this->view('me_password');
	}

	/**
	 * 提交修改密码
	**/
	public function pass_submit_f()
	{
		$oldpass = $this->get("oldpass");
		if(!$oldpass){
			$this->error(P_Lang('管理员密码验证不能为空'));
		}
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'));
		if(!$rs){
			$this->error(P_Lang('管理员信息不存在'));
		}
		if(!password_check($oldpass,$rs["pass"])){
			$this->error(P_Lang("管理员密码不正确"));
		}
		$newpass = $this->get("newpass");
		$array = array();
		if($newpass){
			$chkpass = $this->get("chkpass");
			if(!$chkpass){
				$this->error(P_Lang('确认密码不能为空'));
			}
			if($newpass != $chkpass){
				$this->error(P_Lang("两次输入的新密码不一致"));
			}
			$array['pass'] = password_create($newpass);
		}
		$vpass = $this->get('vpass');
		if($vpass){
			if($vpass == $oldpass){
				$this->error(P_Lang('二次密码不能和旧密码一样'));
			}
			if($newpass && $vpass == $newpass){
				$this->error(P_Lang('二次密码不能和新密码一样'));
			}
			$array['vpass'] = md5(md5($vpass));
		}
		if(!$array['pass'] && !$array['vpass']){
			$this->error(P_Lang('密码或是二次密码至少要填写一个'));
		}
		$this->model('admin')->save($array,$this->session->val('admin_id'));
		$info = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->session->assign('admin_rs',$info);
		$this->success();
	}

	/**
	 * 提交修改个人信息
	**/
	public function submit_f()
	{
		$this->config('is_ajax',true);
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'));
		if(!$rs){
			$this->error(P_Lang('管理员信息不存在'));
		}
		$array = array();
		$name = $this->get('name');
		if(!$name){
			$name = $rs['account'];
		}
		if($name && $name != $rs['account']){
			$check = $this->model('admin')->check_account($name,$this->session->val('admin_id'));
			if($check){
				$this->error(P_Lang('管理员账号已经存在，请重新设置'));
			}
			$array['account'] = $name;
		}
		$email = $this->get('email');
		if($email && $email != $rs['email']){
			$array['email'] = $email;
		}
		$array['fullname'] = $this->get('fullname');
		$this->model('admin')->save($array,$this->session->val('admin_id'));
		$info = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->session->assign('admin_rs',$info);
		$this->success();
	}
}