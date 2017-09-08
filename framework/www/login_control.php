<?php
/**
 * 会员登录操作，基于WEB模式
 * @package phpok\www
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
	public function __construct()
	{
		parent::control();
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->success(P_Lang('您已是本站会员，不需要再次登录'),$backurl);
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
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('网站关闭');
			$this->error($tips,$backurl,10);
		}
		$type = $this->get('type');
		if(!$type){
			$type = $this->site['login_type'];
		}
		$tplfile = 'login';
		if($type){
			$tmp = $this->model('site')->tpl_file('login',$type);
			if($tmp){
				$tplfile = $tmp;
			}else{
				if($this->tpl->check('login_'.$type) && $this->model('gateway')->get_default($type)){
					$tplfile = 'login_'.$type;
				}
			}
		}
		$logintype = array('sms'=>false,'email'=>false);
		if($this->model('gateway')->get_default('sms')){
			$logintype['sms'] = true;
		}
		if($this->model('gateway')->get_default('email')){
			$logintype['email'] = true;
		}
		$this->assign("_back",$backurl);
		$this->assign('logintype',$logintype);
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$this->view($tplfile);
	}

	/**
	 * 基于WEB的登录模式，有返回有跳转，适用于需要嵌入第三方HTML代码使用
	 * @参数 _back 返回之前登录后的页面
	 * @参数 _chkcode 验证码，根据实际情况判断是否启用此项
	 * @参数 user 会员账号/邮箱/手机号
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
			$this->success(P_Lang('您已是本站会员，不需要再次登录'),$_back);
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
			$this->error(P_Lang('会员密码不能为空'),$error_url);
		}
		//多种登录方式
		$user_rs = $this->model('user')->get_one($user,'user');
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'email');
			if(!$user_rs){
				$user_rs = $this->model('user')->get_one($user,'mobile');
				if(!$user_rs){
					$this->error(P_Lang('会员信息不存在'),$error_url);
				}
			}
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('会员审核中，暂时不能登录'),$error_url);
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('会员被管理员锁定，请联系管理员解锁'),$error_url);
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登录密码不正确'),$error_url);
		}
		$this->session->assign('user_id',$user_rs['id']);
		$this->session->assign('user_gid',$user_rs['group_id']);
		$this->session->assign('user_name',$user_rs['user']);
		//接入财富
		$this->model('wealth')->login($user_rs['id'],P_Lang('会员登录'));
		$this->success(P_Lang('会员登录成功'),$_back);
	}

	/**
	 * 弹出窗口登录页
	**/
	public function open_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不需要再次登录'));
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
		$this->view($tplfile);
	}

	/**
	 * 短信验证码重置密码
	**/
	public function smspass_f()
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
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_smspass';
		}
		$this->view($tplfile);
	}

	/**
	 * 取回密码
	 * @参数 _back 返回之前页面
	**/
	public function getpass_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
			$error_url = $this->url('login');
		}else{
			$error_url = $this->url('login','','_back='.rawurlencode($_back));
		}
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不能执行这个操作'),$_back);
		}
		$server = $this->model('gateway')->get_default('email');
		$sms_server = $this->model('gateway')->get_default('sms');
		if(!$server && !$sms_server){
			$this->error(P_Lang('未配置好邮件/短信通知功能，请联系管理员'),$error_url,10);
		}
		$getpasstype = array('sms'=>false,'email'=>false);
		if($sms_server){
			$getpasstype['sms'] = true;
		}
		if($server){
			$getpasstype['email'] = true;
		}
		$this->assign('getpasstype',$getpasstype);
		if(!$server){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,'smspass');
			if(!$tplfile){
				$tplfile = 'login_smspass';
			}
			$this->view($tplfile);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_getpass';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','getpass'));
		$this->view($tplfile);
	}

	/**
	 * 重置密码操作
	 * @参数 _back 返回之前跳转的页面
	 * @参数 _code 险证码
	**/
	public function repass_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
			$error_url = $this->url('login');
		}else{
			$error_url = $this->url('login','','_back='.rawurlencode($_back));
		}
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不能执行这个操作'),$_back);
		}
		$code = $this->get('_code');
		if($code){
			$time = intval(substr($code,-10));
			if(($this->time - $time) > (24*60*60)){
				$this->error(P_Lang('验证码超时过期，请重新获取'),$this->url('login','getpass'),10);
			}
			$uid = $this->model('user')->uid_from_chkcode($code);
			if(!$uid){
				$this->error(P_Lang('验证码不存在'),$this->url('login','getpass'),10);
			}
			$this->assign('code',$code);
			$user = $this->model('user')->get_one($uid);
			$this->assign("user",$user);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_repass';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','getpass'));
		$this->view($tplfile);
	}
}