<?php
/**
 * 管理员登录
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年05月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("admin");
		$this->model("site");
	}

	/**
	 * 登录页面
	**/
	public function index_f()
	{
		if($this->session->val('admin_id')){
			$this->error(P_Lang('您已成功登录'),$this->url('index'));
		}
		$vcode = ($this->config["is_vcode"] && function_exists("imagecreate")) ? true : false;
		$this->assign("vcode",$vcode);
		$multiple_language = isset($this->config['multiple_language']) ? $this->config['multiple_language'] : false;
		if($multiple_language){
			$langlist = $this->model('lang')->get_list();
			$this->assign('langlist',$langlist);
			$this->assign('langid',$this->session->val('admin_lang_id'));
		}
		$this->assign('multiple_language',$multiple_language);
		$logo = $this->site['adm_logo180'] ? $this->site['adm_logo180'] : '';
		$this->assign('logo',$logo);
		$cdnUrl = phpok_cdn();
		$this->assign('phpok_cdn_link',$cdnUrl);
		$this->view('login');
	}

	private function lock_action($user='',$error_add=false)
	{
		$lockfile = $this->dir_cache.'lock-'.$this->session->sessid().'-admin.php';
		if(!file_exists($lockfile)){
			$lock_count = 0;
			$lock_time = $this->time;
		}else{
			$info = $this->lib('file')->cat($lockfile);
			if($info){
				$tmp = explode(",",$info);
				$lock_time = $tmp[0];
				$lock_count = $tmp[1] ? intval($tmp[1]) : 0;
			}else{
				$lock_count = 0;
				$lock_time = $this->time;
			}
		}
		$lock_time_config = intval($this->config['lock_time'] ? $this->config['lock_time'] : 2) * 3600;
		if(!$lock_time_config){
			$lock_time_config = 7200;
		}
		if(($this->time - $lock_time_config) > $lock_time){
			$lock_count = 0;
			$lock_time = $this->time;
		}
		//管理员
		$max_lock_count = $this->config['lock_error_count'] ? intval($this->config['lock_error_count']) : 5;
		if(!$max_lock_count){
			$max_lock_count = 5;
		}
		if($lock_count>=$max_lock_count){
			if($user && !is_bool($user)){
				$check = $this->model('admin')->account_lock_check($user);
				if(!$check){
					$this->model('admin')->account_lock($user);
				}else{
					$time = date("Y-m-d H:i:s",$check['unlock_time']);
					$this->error(P_Lang('管理员账户系统锁定，解锁时间是 {time}',array('time'=>$time)));
				}
			}else{
				//针对IP数据进行较验
				$ip_lock = $this->model('admin')->ip_lock_check();
				if(!$ip_lock){
					$this->model('admin')->ip_lock();
				}else{
					$time = date("Y-m-d H:i:s",$ip_lock['unlock_time']);
					$this->error(P_Lang('管理员账户系统锁定，解锁时间是 {time}',array('time'=>$time)));
				}
				//针对SESSION数据进行较验
				$session_lock = $this->model('admin')->session_lock_check();
				if(!$session_lock){
					$this->model('admin')->session_lock();
				}else{
					$time = date("Y-m-d H:i:s",$session_lock['unlock_time']);
					$this->error(P_Lang('管理员账户系统锁定，解锁时间是 {time}',array('time'=>$time)));
				}
			}
			$this->error(P_Lang('登录错误次数超过{count}次了，系统锁定两小时',array('count'=>$max_lock_count)));
		}
		if($error_add || ($user && is_bool($user))){
			$lock_count++;
		}
		$this->lib('file')->vi($lock_time.','.$lock_count,$lockfile);
	}

	public function ok_f()
	{
		if($this->session->val('admin_id')){
			$this->error(P_Lang('您已成功登录，无需再次验证'));
		}
		$user = $this->get('user');
		if(!$user){
			$this->lock_action(true);
			$this->error(P_Lang('管理员账号不能为空'));
		}
		$this->lock_action($user,false);
		
		//检查 2 小时内该账户是否有系统锁定
		$check = $this->model('admin')->account_lock_check($user);
		if($check){
			if($check['unlock_time'] > $this->time){
				$time = date("Y-m-d H:i:s",$check['unlock_time']);
				$this->error(P_Lang('管理员账户系统锁定，解锁时间是-1 {time}',array('time'=>$time)));
			}
			$this->model('admin')->lock_delete($user);			
		}
		
		$pass = $this->get('pass');
		if(!$pass){
			$this->lock_action($user,true);
			$this->error(P_Lang('密码不能为空'));
		}
		//验证码检测
		if($this->config['is_vcode'] && function_exists('imagecreate')){
			$code = $this->get("_code");
			if(!$code){
				$this->error(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->lock_action($user,true);
				$this->error(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode_admin');
		}
		
		$rs = $this->model('admin')->get_one_from_name($user);
		if(!$rs){
			$this->lock_action($user,true);
			$this->error(P_Lang('管理员不存在或密码不正确'));
		}
		if(!password_check($pass,$rs["pass"])){
			$this->lock_action($user,true);
			$this->error(P_Lang('管理员不存在或密码不正确'));
		}
		if(!$rs["status"]){
			$this->lock_action($user,true);
			$this->error(P_Lang("管理员不存在或密码不正确"));
		}
		//获取管理员的权限
		$this->session->assign('admin_site_id',$this->site['id']);
		if(!$rs["if_system"]){
			$popedom_list = $this->model('admin')->get_popedom_list($rs["id"]);
			if(!$popedom_list){
				$this->error(P_Lang('你的管理权限未设置好，请联系超级管理员进行设置'));
			}
			$this->session->assign('admin_popedom',$popedom_list);
			$site_id = $this->model('popedom')->get_site_id($popedom_list);
			if(!$site_id){
				$this->error(P_Lang('你的管理权限未设置好，请联系超级管理员进行设置'));
			}
			$this->session->assign('admin_site_id',$site_id);
		}
		$this->session->assign('admin_id',$rs['id']);
		$this->session->assign('admin_account',$rs['account']);
		$this->session->assign('admin_rs',$rs);
		if($this->config['develop']){
			$this->session->assign('adm_develop',true);
		}
		//删除锁定
		$this->model('admin')->lock_delete($rs['account']);
		$this->success(P_Lang('管理员登录成功'));
	}

	private function _domain()
	{
		$domain = $this->lib('server')->domain($this->config['get_domain_method']);
		if(!$domain){
			$tmp = strtoupper($this->config['get_domain_method']) == 'HTTP_HOST' ? 'SERVER_NAME' : 'HTTP_HOST';
			$domain = $this->lib('server')->domain($tmp);
		}
		return $domain;
	}

	//生成二维码需要的字串
	public function qrcode_f()
	{
		$data = array();
		$data['ip'] = $this->lib('common')->ip();
		$data['time'] = $this->time;
		$data['code'] = $this->lib('common')->str_rand(10,'letter');
		$data['domain'] = $this->_domain();
		$keyid = $this->lib('common')->str_rand(16);
		$fid = 'a'.$this->lib('common')->str_rand(31);
		$this->lib('token')->keyid($keyid);
		$content = $this->lib('token')->encode($data);
		$this->lib("file")->vi($keyid,$this->dir_cache.$fid.'.php');
		$tmp = array('fid'=>$fid,'content'=>$content,'code'=>md5($data['domain']));
		$this->session->assign('admin_qrcode_code',$data['code']);
		$this->success($tmp);
	}

	public function mobile_f()
	{
		$fid = $this->get('fid','system');
		if(!$fid){
			$this->error('未指定FID或FID不合法');
		}
		$content = $this->get('content','html');
		if(!$fid || !$content){
			$this->error(P_Lang('数据异常，仅限扫码接入，不支持直接访问'));
		}
		$file = $this->dir_cache.$fid.'.php';
		if(!file_exists($file)){
			$this->error(P_Lang('验证文件丢失，请重新扫码'));
		}
		$keyid = $this->lib('file')->cat($file);
		$this->lib('token')->keyid($keyid);
		$data = $this->lib('token')->decode($content);
		if(!$data){
			$this->error(P_Lang('内容丢失，请重新扫码'));
		}
		if(!is_array($data) || !$data['ip'] || !$data['code'] || !$data['time']){
			$this->error(P_Lang('数据异常，请重新扫码'));
		}
		//增加状态文件
		$this->lib('file')->vi($this->time,$this->dir_cache.$fid.'-checking.php');
		$this->assign('fid',$fid);
		$this->assign('fcode',$data['code']);
		$this->view('login-mobile');
	}

	/**
	 * 快速验证账号是否一致
	**/
	public function checkadm_f()
	{
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("后台未设置解码密钥"));
		}
		$fid = $this->get('fid');
		if(!$fid){
			$this->error(P_Lang('未指定验证ID'));
		}
		$file = $this->dir_cache.$fid.'.php';
		if(!file_exists($file)){
			$this->error(P_Lang('验证文件丢失，请重新扫码'));
		}
		$content = $this->get('content','html');
		if(!$content){
			$this->error(P_Lang('内容不能为空'));
		}
		$this->lib('token')->keyid($api_code);
		$msg = $this->lib('token')->decode($content);
		if(!$msg || !is_array($msg) || !$msg['id'] || !$msg['user'] || !$msg['time'] || !$msg['domain']){
			$this->error(P_Lang('数据解码失败'));
		}
		//超过30天，告知无效
		$time = $this->time - $msg['time'];
		if($time>(30*24*60*60)){
			$this->error(P_Lang('数据超过30天，自动无效'));
		}
		$domain = $this->_domain();
		if($msg['domain'] != $domain){
			$this->error(P_Lang('数据来源不准确'));
		}
		$rs = $this->model('admin')->get_one($msg['id']);
		if(!$rs || $rs['account'] != $msg['user']){
			$this->error(P_Lang('账号不一致'));
		}
		$data = array('id'=>$rs['id'],'user'=>$rs['account'],'time'=>$this->time);
		$data['domain'] = $domain;
		//基于临时密码生成账号密串
		$keyid = $this->lib('file')->cat($file);
		$this->lib('token')->keyid($keyid);
		$this->lib('token')->expiry(300);
		$logincode = $this->lib('token')->encode($data);
		$this->success(array('account'=>$rs['account'],'logincode'=>$logincode));
	}

	public function mobile_success_f()
	{
		$this->success('管理员登录成功，请手动关闭当前页面');
	}

	public function update_f()
	{
		$login_time = $this->get('login_time');
		if(!$login_time){
			$login_time = 1440;
		}
		$fid = $this->get('fid','system');
		$fcode = $this->get('fcode','system');
		if(!$fid && !$fcode){
			$this->error(P_Lang('登录数据不完整'));
		}
		$quickcode = $this->get('quickcode','html');
		if($quickcode){
			$file = $this->dir_cache.$fid.'.php';
			if(!file_exists($file)){
				$this->error(P_Lang('验证文件丢失，请重新扫码'));
			}
			$keyid = $this->lib('file')->cat($file);
			$this->lib('token')->keyid($keyid);
			$msg = $this->lib('token')->decode($quickcode);
			if(!$msg || !is_array($msg) || !$msg['id'] || !$msg['user'] || !$msg['time'] || !$msg['domain']){
				$this->error(P_Lang('数据解码失败'));
			}
			$msg['id'] = intval($msg['id']);
			if(!$msg['id']){
				$this->error(P_Lang('数据不正确，请检查'));
			}
			//超过30天，告知无效
			$time = $this->time - $msg['time'];
			if($time>(30*24*60*60)){
				$this->error(P_Lang('数据超过30天，请重新登录'));
			}
			$domain = $this->_domain();
			if($msg['domain'] != $domain){
				$this->error(P_Lang('数据来源不准确'));
			}
			$rs = $this->model('admin')->get_one($msg['id']);
			if(!$rs || $rs['account'] != $msg['user']){
				$this->error(P_Lang('账号不一致'));
			}
		}else{
			$user = $this->get('user');
			$pass = $this->get('pass');
			if(!$user || !$pass){
				$this->error(P_Lang('账号/密码不能为空'));
			}
			$rs = $this->model('admin')->get_one_from_name($user);
			if(!$rs){
				$this->error(P_Lang('管理员信息不存在'));
			}
			if(!$rs["status"]){
				$this->error(P_Lang("管理员账号已被锁定，请联系超管"));
			}
			if(!password_check($pass,$rs["pass"])){
				$this->error(P_Lang('管理员密码输入不正确'));
			}
		}
		$domain = $this->_domain();
		$data = array('id'=>$rs['id'],'user'=>$rs['account'],'time'=>$this->time);
		$data['domain'] = $domain;
		$data['online'] = $login_time;
		//删除checking文件，创建登录文件
		$this->lib('file')->rm($this->dir_cache.$fid.'-checking.php');
		$this->lib('file')->vi($this->lib('json')->encode($data),$this->dir_cache.$fid.'-'.$fcode.'.php');
		$this->success($content);
	}

	public function checking_f()
	{
		$fid = $this->get('fid');
		if(!$fid){
			$this->error(P_Lang('未指定验证ID'));
		}
		$content = $this->get('content','html');
		if(!$content){
			$this->error(P_Lang('内容不能为空'));
		}
		if(!$this->session->val('admin_qrcode_code')){
			$this->error(P_Lang('获取密钥失败，请重新扫码'));
		}
		$file = $this->dir_cache.$fid.'.php';
		if(!file_exists($file)){
			$this->error(P_Lang('验证文件丢失，请重新扫码'));
		}
		$keyid = $this->lib('file')->cat($file);
		$this->lib('token')->keyid($keyid);
		$data = $this->lib('token')->decode($content);
		if(!$data){
			$this->error(P_Lang('内容丢失，请重新扫码'));
		}
		if(!is_array($data) || !$data['ip'] || !$data['code'] || !$data['time']){
			$this->error(P_Lang('数据异常，请重新扫码'));
		}
		//忽略IP检测，因为经常出现不一致
		if($data['code'] != $this->session->val('admin_qrcode_code')){
			$this->error(P_Lang('密钥异常，请重新扫码登录'));
		}
		$used_time = $this->time - $data['time'];
		$expire_time = 300;
		if($this->config['admin_qrcode_expire_time'] && $this->config['admin_qrcode_expire_time']>120){
			$expire_time = $this->config['admin_qrcode_expire_time'];
		}
		if($used_time > $expire_time){
			$this->error(P_Lang('登录超时，请刷新重新扫码'));
		}
		//检测是否已登录
		$login_file = $this->dir_cache.$fid.'-'.$data['code'].'.php';
		if(!file_exists($login_file)){
			$check_file = $this->dir_cache.$fid.'-checking.php';
			if(file_exists($check_file)){
				$this->tip(1);
			}
			$this->tip(P_Lang('等待手机端扫码'));
		}
		$info = $this->lib('file')->cat($login_file);
		if(!$info){
			$this->error(P_Lang('登录失败，请重新扫码'));
		}
		$c_info = $this->lib('json')->decode($info);
		if(!$c_info || !$c_info['id'] || !$c_info['user'] || !$c_info['time']){
			$this->error(P_Lang('登录失败，请重新扫码'));
		}
		$rs = $this->model('admin')->get_one($c_info['id']);
		if(!$rs){
			$this->error(P_Lang('管理员不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('管理员已被锁定'));
		}
		if($rs['account'] != $c_info['user']){
			$this->error(P_Lang('账号不一致，请检查'));
		}
		//获取管理员的权限
		$this->session->assign('admin_site_id',$this->site['id']);
		if(!$rs["if_system"]){
			$popedom_list = $this->model('admin')->get_popedom_list($rs["id"]);
			if(!$popedom_list){
				$this->error(P_Lang('你的管理权限未设置好，请联系超级管理员进行设置'));
			}
			$this->session->assign('admin_popedom',$popedom_list);
			$site_id = $this->model('popedom')->get_site_id($popedom_list);
			if(!$site_id){
				$this->error(P_Lang('你的管理权限未设置好，请联系超级管理员进行设置'));
			}
			$this->session->assign('admin_site_id',$site_id);
		}
		$this->session->assign('admin_id',$rs['id']);
		$this->session->assign('admin_account',$rs['account']);
		$this->session->assign('admin_rs',$rs);
		if($c_info['online']){
			$this->session->assign('admin_login_time',$this->time);
			$this->session->assign('admin_long_time',$c_info['online']);
		}
		if($this->config['develop']){
			$this->session->assign('adm_develop',true);
		}
		$this->model('admin')->lock_delete($rs['account']);
		$this->lib('file')->rm($login_file);
		$this->lib('file')->rm($file);
		$this->lib('file')->rm($this->dir_cache.$fid.'-checking.php');
		$this->success(P_Lang('管理员登录成功'));
	}
}