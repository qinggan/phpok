<?php
/**
 * 用户登录，基于API请求
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
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
	 * 邮件验证码登录模式
	 * @参数 type 执行方式，当为getcode表示取得验证码，其他为登录验证
	 * @参数 email 邮箱
	 * @参数 _chkcode 验证码
	 * @返回 JSON数据
	**/
	public function email_f()
	{
		$email = $this->get('email');
		if(!$email){
			$this->error(P_Lang('Email不能为空'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->error(P_Lang('Email地址不符合要求'));
		}
		$rs = $this->model('user')->get_one($email,'email',false,false);
		if(!$rs){
			$this->error(P_Lang('Email地址不存在'));
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('图片验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('图片验证码填写不正确'));
			}
			$this->session->unassign('vcode');
			$vcode = $this->get('_vcode');
		}else{
			$vcode = $this->get('_vcode');
			if(!$vcode){
				$vcode = $this->get('_chkcode');
			}
		}
		if(!$vcode){
			$this->error(P_Lang('邮箱验证码不能为空'));
		}
		$this->model('vcode')->type('email');
		$data = $this->model('vcode')->check($vcode);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$array = $this->model('user')->login($rs,true);
		if(!$array){
			$this->error(P_Lang('登录失败'));
		}
		$array['token'] = $this->control('token','api')->user_token($rs['id']);
		$this->plugin('plugin-login-email',$user_rs['id']);
		$this->model('vcode')->delete();
		$this->success($array);
	}

	/**
	 * 用户登录别名
	**/
	public function index_f()
	{
		$this->save_f();
	}

	/**
	 * 通过取回密码进行修改
	 * @参数 mobile 手机号（与邮箱必须有一个）
	 * @参数 email 邮箱（与手机号中必须有一个）
	 * @参数 _chkcode 手机验证码或邮箱验证码
	 * @参数 newpass 新密码
	 * @参数 chkpass 确认密码
	**/
	public function repass_f()
	{
		$type_id = $this->get('type_id');
		if(!$type_id || !in_array($type_id,array('email','sms'))){
			$this->error(P_Lang('仅支持邮件或短信重设密码'));
		}
		if($this->model('site')->vcode('system','getpass')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('图片验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('图片验证码填写不正确'));
			}
			$this->session->unassign('vcode');
			$vcode = $this->get('_vcode');
		}else{
			$vcode = $this->get('_vcode');
			if(!$vcode){
				$vcode = $this->get('_chkcode');
			}
		}
		if($type_id == 'email'){
			$email = $this->get('email');
			if(!$email){
				$this->error(P_Lang('Email不能为空'));
			}
			if(!$this->lib('common')->email_check($email)){
				$this->error(P_Lang('Email地址不符合要求'));
			}
			
			$this->model('vcode')->type('email');
			$user_rs = $this->model('user')->get_one($email,'email',false,false);
		}else{
			$mobile = $this->get('mobile');
			if(!$mobile){
				$this->error(P_Lang('手机号不能为空'));
			}
			if(!$this->lib('common')->tel_check($mobile,'mobile')){
				$this->error(P_Lang('手机号不符合格式要求'));
			}
			$this->model('vcode')->type('sms');
			$user_rs = $this->model('user')->get_one($mobile,'mobile',false,false);
		}
		if(!$user_rs){
			$this->error(P_Lang('用户信息不存在'));
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('用户账号审核中，暂时不能使用取回密码功能'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('用户账号被管理员锁定，不能使用取回密码功能，请联系管理员'));
		}
		if(!$vcode){
			$this->error(P_Lang('验证码不能为空'));
		}
		$data = $this->model('vcode')->check($vcode);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->error(P_Lang('密码不能为空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->error(P_Lang('确认密码不能为空'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('两次输入的密码不一致'));
		}
		$pass = password_create($newpass);
		$this->model('user')->update_password($pass,$user_rs['id']);
		$this->model('vcode')->delete();
		$this->success();
	}

	/**
	 * 用户登录接口
	 * @参数 _chkcode 验证码
	 * @参数 user 用户账号/邮箱/手机号
	 * @参数 pass 用户密码
	**/
	public function save_f()
	{
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('验证码不正确'));
			}
			$this->session->unassign('vcode');
		}
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('账号/邮箱/手机号不能为空'));
		}
		$pass = $this->get("pass");
		if(!$pass){
			$this->error(P_Lang('密码不能为空'));
		}
		if($this->lib('common')->email_check($user)){
			$user_rs = $this->model('user')->get_one($user,'email');
		}
		if(!$user_rs && $this->lib('common')->tel_check($user,'mobile')){
			$user_rs = $this->model('user')->get_one($user,'mobile');
		}
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'user');
		}
		if(!$user_rs){
			$this->error(P_Lang('用户信息不存在'));
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('用户审核中，暂时不能登录'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('用户被管理员锁定，请联系管理员解锁'));
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登录密码不正确'));
		}
		$array = $this->model('user')->login($user_rs,true);
		if(!$array){
			$this->error(P_Lang('登录失败'));
		}
		$_back = $this->get('_back');
		if(!$_back){
			$_back = $this->url('usercp','','www',true);
		}
		$this->plugin('plugin-login-save',$user_rs['id']);
		$array['token'] = $this->control('token','api')->user_token($user_rs['id']);
		$this->success($array,$_back);
	}

	/**
	 * 短信验证码登录，此项登录不需要图形再输入图形验证码，验证码有效期时间是10分钟
	 * @参数 type 执行方式，当为getcode表示取得验证码，其他为登录验证
	 * @参数 mobile 手机号
	 * @参数 _chkcode 验证码（type不为空时有效）
	 * @返回 JSON数组
	**/
	public function sms_f()
	{
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->error(P_Lang('手机号不能为空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->error(P_Lang('手机号不正确'));
		}
		$rs = $this->model('user')->get_one($mobile,'mobile',false,false);
		if(!$rs){
			$this->error(P_Lang('手机号不存在'));
		}
		$user = $this->model('user')->get_one($mobile,'mobile');
		if(!$user){
			$this->error(P_Lang('您登录的账号信息不存在'));
		}
		if(!$user['status']){
			$this->model('user')->logout();
			$this->error(P_Lang('您的注册信息未审核通过，请与管理员联系'));
		}
		if($user['status'] == '2'){
			$this->model('user')->logout();
			$this->error(P_Lang('您的账号被锁定，请与管理员联系'));
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('图片验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('图片验证码填写不正确'));
			}
			$this->session->unassign('vcode');
			$vcode = $this->get('_vcode');
		}else{
			$vcode = $this->get('_vcode');
			if(!$vcode){
				$vcode = $this->get('_chkcode');
			}
		}
		if(!$vcode){
			$this->error(P_Lang('手机验证码不能为空'));
		}
		$this->model('vcode')->type('sms');
		$data = $this->model('vcode')->check($vcode);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$array = $this->model('user')->login($rs,true);
		if(!$array){
			$this->error(P_Lang('用户登录失败'));
		}
		$array['token'] = $this->control('token','api')->user_token($rs['id']);
		$this->model('vcode')->delete();
		$this->success($array);
	}

	/**
	 * 登录状态判断
	**/
	public function status_f()
	{
		if($this->session->val('user_id')){
			$this->success();
		}
		$this->error(P_Lang('未登录'));
	}

	/**
	 * 用户自动登录，主要是通过验签码，实现单点登录
	 * @参数 token 单点登录参数，一般用于放在cookie里
	**/
	public function auto_f()
	{
		$token = $this->get('token','html');
		if(!$token){
			$this->error(P_Lang('未指定参数'));
		}
		$user = $this->control('token','api')->token2user($token);
		if(!$user){
			$this->error('自动登录失败');
		}
		$data = $this->model('user')->login($user,true);
		$token = $this->control('token','api')->user_token($user['id']);
		if($token){
			$data['token'] = $token;
		}
		$this->success($data);
	}
}