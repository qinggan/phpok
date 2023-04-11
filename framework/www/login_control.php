<?php
/**
 * 用户登录操作，基于WEB模式
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->url('usercp');
		}
		if($this->session->val('user_id')){
			$this->_location($backurl);
		}
	}

	/**
	 * 登录页
	 * @参数 _back 返回上一级的页面链接地址
	 * @返回 
	 * @更新时间 
	**/
	public function index_f()
	{
		$backurl = $this->get('_back');
		if($backurl){
			$tmp = strtolower($backurl);
			if(substr($tmp,0,7) != 'http://' && substr($tmp,0,8) != 'https://'){
				$backurl = $this->config['url'].$backurl;
			}
			$backup_host = parse_url($backurl,PHP_URL_HOST);
			$webhost = $this->lib('server')->domain();
			if($backup_host != $webhost){
				$backurl = $this->config['url'];
			}
		}else{
			$backurl = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->_location($backurl);
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			$this->error($tips);
		}
		$tplfile = $this->model('site')->tpl_file('login','index');
		if(!$tplfile){
			$tplfile = 'login';
		}
		$this->assign("_back",$backurl);
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$check_sms = $this->model('gateway')->get_default('sms');
		$this->assign('login_sms',($check_sms && $this->site['login_type_sms']) ? true : false);
		$check_email = $this->model('gateway')->get_default('email');
		$this->assign('login_email',($check_email && $this->site['login_type_email']) ? true : false);
		$this->view($tplfile);
	}

	/**
	 * 短信验证登录
	**/
	public function sms_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			$this->error($tips);
		}
		$chk = $this->model('gateway')->get_default('sms');
		if(!$chk){
			$this->error(P_Lang('没有安装默认短信发送引挈，请先安装并设置一个默认'),$backurl);
		}
		if(!$this->site['login_type_sms']){
			$this->error(P_Lang('未设置短信模板'),$backurl);
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$tplfile = $this->model('site')->tpl_file('login','sms');
		if(!$tplfile){
			$tplfile = 'login_sms';
		}
		$check_email = $this->model('gateway')->get_default('email');
		$this->assign('login_email',$check_email);
		$this->view($tplfile);
	}

	/**
	 * 邮件验证码登录
	**/
	public function email_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			$this->error($tips);
		}
		$chk = $this->model('gateway')->get_default('email');
		if(!$chk){
			$this->error(P_Lang('没有安装默认邮件发送引挈，请先安装并设置一个默认'),$backurl);
		}
		if(!$this->site['login_type_email']){
			$this->error(P_Lang('未设置邮件模板'),$backurl);
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$tplfile = $this->model('site')->tpl_file('login','email');
		if(!$tplfile){
			$tplfile = 'login_email';
		}
		$check_sms = $this->model('gateway')->get_default('sms');
		$this->assign('login_sms',$check_sms);
		$this->view($tplfile);
	}

	/**
	 * 基于WEB的登录模式，有返回有跳转，适用于需要嵌入第三方HTML代码使用
	 * @参数 _back 返回之前登录后的页面
	 * @参数 _chkcode 验证码，根据实际情况判断是否启用此项
	 * @参数 user 用户账号/邮箱/手机号
	 * @参数 pass 密码
	**/
	public function ok_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
			$error_url = $this->url('login');
		}else{
			$error_url = $this->url('login','','_back='.rawurlencode($_back));
		}
		if($this->session->val('user_id')){
			$this->success(P_Lang('您已是本站用户，不需要再次登录'),$_back);
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('验证码不能为空'),$error_url);
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('验证码填写不正确'),$error_url);
			}
			$this->session->unassign('vocode');
		}
		//获取登录信息
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('账号不能为空'),$error_url);
		}
		$pass = $this->get("pass");
		if(!$pass){
			$this->error(P_Lang('用户密码不能为空'),$error_url);
		}
		//多种登录方式
		$user_rs = $this->model('user')->get_one($user,'user');
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'email');
			if(!$user_rs){
				$user_rs = $this->model('user')->get_one($user,'mobile');
				if(!$user_rs){
					$this->error(P_Lang('用户信息不存在'),$error_url);
				}
			}
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('用户审核中，暂时不能登录'),$error_url);
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('用户被管理员锁定，请联系管理员解锁'),$error_url);
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登录密码不正确'),$error_url);
		}
		$this->session->assign('user_id',$user_rs['id']);
		$this->session->assign('user_gid',$user_rs['group_id']);
		$this->session->assign('user_name',$user_rs['user']);
		$this->session->assign('user_status',$user_rs['status']);
		//接入财富
		$this->model('wealth')->login($user_rs['id'],P_Lang('用户登录'));
		$this->success(P_Lang('用户登录成功'),$_back);
	}

	/**
	 * 弹出窗口登录页
	**/
	public function open_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站用户，不需要再次登录'));
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			$this->error($tips);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_open';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$email = $this->get('email');
		if($email){
			$this->assign('email',$email);
		}
		$mobile = $this->get('mobile');
		if($mobile){
			$this->assign('mobile',$mobile);
		}
		$user = $this->get('user');
		if($user){
			$this->assign('user',$user);
		}
		$accout = $user ? $user : ($mobile ? $mobile : $email);
		$this->assign('accout',$accout);
		$this->view($tplfile);
	}

	/**
	 * 取回密码
	**/
	public function getpass_f()
	{
		$server = $this->model('gateway')->get_default('email');
		$sms_server = $this->model('gateway')->get_default('sms');
		if(!$server && !$sms_server){
			$this->error(P_Lang('未配置好邮件/短信通知功能，请联系管理员'),$this->url);
		}
		if($server && $this->site['login_type_email']){
			$this->assign('check_email',true);
		}
		if($sms_server && $this->site['login_type_sms']){
			$this->assign('check_sms',true);
		}
		$type_id = $this->get('type_id');
		if(!$type_id || !in_array($type_id,array('email','sms'))){
			$type_id = ($server && $this->site['login_type_email']) ? 'email' : 'sms';
		}
		$this->assign('type_id',$type_id);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_getpass';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','getpass'));
		$this->view($tplfile);
	}
}