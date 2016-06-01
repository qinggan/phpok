<?php
/***********************************************************
	Filename: {phpok}/www/login_control.php
	Note	: 会员登录操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年07月01日 05时33分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->url;
		}
		if($_SESSION["user_id"]){
			error(P_Lang('您已是本站会员，不需要再次登录'),$_back);
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			error($tips,$_back,'error',10);
		}
		$this->assign("_back",$_back);
		$this->view("login");
	}

	//登录，基于HTML模式
	public function ok_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->url;
			$error_url = $this->url('login');
		}else{
			$error_url = $this->url('login','','_back='.rawurlencode($_back));
		}
		if($_SESSION["user_id"]){
			error(P_Lang('您已是本站会员，不需要再次登录'),$_back);
		}
		if($this->config['is_vcode'] && function_exists('imagecreate')){
			$code = $this->get('_chkcode');
			if(!$code){
				error(P_Lang('验证码不能为空'),$error_url,'error');
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode']){
				error(P_Lang('验证码填写不正确'),$error_url,'error');
			}
			unset($_SESSION['vcode']);
		}
		//获取登录信息
		$user = $this->get("user");
		if(!$user){
			error(P_Lang('账号不能为空'),$error_url,'error');
		}
		$pass = $this->get("pass");
		if(!$pass){
			error(P_Lang('会员密码不能为空'),$error_url,'error');
		}
		//多种登录方式
		$user_rs = $this->model('user')->get_one($user,'user');
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'email');
			if(!$user_rs){
				$user_rs = $this->model('user')->get_one($user,'mobile');
				if(!$user_rs){
					error(P_Lang('会员信息不存在'),$error_url,'error');
				}
			}
		}
		if(!$user_rs['status']){
			error(P_Lang('会员审核中，暂时不能登录'),$error_url,'error');
		}
		if($user_rs['status'] == '2'){
			error(P_Lang('会员被管理员锁定，请联系管理员解锁'),$error_url,'error');
		}
		if(!password_check($pass,$user_rs["pass"])){
			error(P_Lang('登录密码不正确'),$error_url,'error');
		}
		$_SESSION["user_id"] = $user_rs['id'];
		$_SESSION["user_gid"] = $user_rs['group_id'];
		$_SESSION["user_name"] = $user_rs["user"];
		error(P_Lang('会员登录成功'),$_back,'ok');
	}

	//弹出窗口
	public function open_f()
	{
		if($_SESSION["user_id"]){
			error(P_Lang('您已是本站会员，不需要再次登录'));
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			error($tips,'','error');
		}
		$this->view("login_open");
	}

	public function getpass_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->url;
			$error_url = $this->url('usercp');
		}else{
			$error_url = $this->url('usercp','','_back='.rawurlencode($_back));
		}
		if($_SESSION["user_id"]){
			error(P_Lang('您已是本站会员，不能执行这个操作'),$error_url);
		}
		$server = $this->model('gateway')->get_default('email');
		if(!$server){
			error(P_Lang('未配置好邮件通知功能，请联系管理员'),$this->url('index'),10);
		}
		$this->view("login_getpass");
	}

	public function repass_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->url;
			$error_url = $this->url('usercp');
		}else{
			$error_url = $this->url('usercp','','_back='.rawurlencode($_back));
		}
		if($_SESSION["user_id"]){
			error(P_Lang('您已是本站会员，不能执行这个操作'),$error_url);
		}
		$_SESSION['repass_spam_code'] = str_rand(10);
		$code = $this->get('_code');
		if($code){
			$time = intval(substr($code,-10));
			if(($this->time - $time) > (24*60*60)){
				error(P_Lang('验证码超时过期，请重新获取'),$this->url('login','getpass'),'error',10);
			}
			$uid = $this->model('user')->uid_from_chkcode($code);
			if(!$uid){
				error(P_Lang('验证码不存在'),$this->url('login','getpass'),'error',10);
			}
			$this->assign('code',$code);
			$user = $this->model('user')->get_one($uid);
			$this->assign("user",$user);
		}
		$this->view('login_repass');
	}

}
?>