<?php
/**
 * 会员登录，基于API请求
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
	 * 会员登录接口
	 * @参数 _chkcode 验证码
	 * @参数 user 会员账号/邮箱/手机号
	 * @参数 pass 会员密码
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不需要再次登录'));
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('验证码填写不正确'));
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
			$this->error(P_Lang('会员信息不存在'));
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('会员审核中，暂时不能登录'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('会员被管理员锁定，请联系管理员解锁'));
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登录密码不正确'));
		}
		$this->model('user')->update_session($user_rs['id']);
		$this->model('wealth')->login($user_rs['id'],P_Lang('会员登录'));
		$_back = $this->get('_back');
		if(!$_back){
			$_back = $this->url('usercp','','www',true);
		}
		//增加节点
		$this->plugin('plugin-login-save',$user_rs['id']);
		$this->success(array('user_id'=>$user_rs['id'],'user_name'=>$user_rs['user'],'user_gid'=>$user_rs['group_id']),$_back);
	}

	/**
	 * 会员登录别名
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
			$this->error(P_Lang('会员账号审核中，暂时不能使用取回密码功能'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('会员账号被管理员锁定，不能使用取回密码功能，请联系管理员'));
		}
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('验证码不能为空'));
		}
		$data = $this->model('vcode')->check($code);
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
		$this->success();
	}

	/**
	 * 登录状态判断
	**/
	public function status_f()
	{
		if($this->session->val('user_id')){
			$array = array('user_id'=>$this->session->val('user_id'));
			$array['user_name'] = $this->session->val('user_name');
			$array['user_gid'] = $this->session->val('user_gid');
			$this->success($array);
		}
		$this->error(P_Lang('会员未登录'));
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
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('验证码不能为空'));
		}
		$this->model('vcode')->type('sms');
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->model('user')->update_session($rs['id']);
		$this->model('wealth')->login($rs['id'],P_Lang('会员登录'));
		$array = array('user_id'=>$rs['id'],'user_name'=>$rs['user'],'user_gid'=>$rs['group_id']);
		$this->success($array);
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
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('验证码不能为空'));
		}
		$this->model('vcode')->type('email');
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->model('user')->update_session($rs['id']);
		$this->model('wealth')->login($rs['id'],P_Lang('会员登录'));
		$array = array('user_id'=>$rs['id'],'user_name'=>$rs['user'],'user_gid'=>$rs['group_id']);
		$this->success($array);
	}
}