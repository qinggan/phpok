<?php
/**
 * 注册接口API
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月27日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class register_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 验证账户是否被使用
	 * @参数 user 用户账号
	 * @参数 email 邮箱
	 * @参数 mobile 手机号
	 * @返回 json字串
	**/
	public function check_f()
	{
		$user = $this->get("user");
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if(!$user && !$email && !$mobile){
			$this->error(P_Lang('账号/手机号/邮箱至少要求一项不能为空'));
		}
		if($user){
			$safelist = array("'",'"','/','\\',';','&',')','(');
			foreach($safelist as $key=>$value){
				if($user && strpos($user,$value) !== false){
					$this->error(P_Lang('用户账号不允许包含字符串：{string}',array('string'=>$value)));
				}
			}
			$chk = $this->model('user')->chk_name($user);
			if($chk){
				$this->error(P_Lang('用户账号已存用'));
			}
		}
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->error(P_Lang('邮箱不合法'));
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->error(P_Lang('邮箱已注册'));
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->error(P_Lang('手机号不合法'));
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->error(P_Lang('手机号已注册'));
			}
		}
		$this->success();
	}

	/**
	 * 注册提交成功信息
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站用户，不能执行这个操作'));
		}
		$group_id = $this->get("group_id","int");
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
			if(!$group_rs['is_open'] && !$group_rs['is_default']){
				$this->error(P_Lang('指定的用户组没有开放申请，请联系管理员'));
			}
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default();
			if(!$group_rs || !$group_rs["status"]){
				$this->json(P_Lang('注册失败，网站未开放注册权限'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->error(P_Lang('注册失败，网站未开放注册权限'));
		}

		$user = $this->get("user");
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if(!$user && !$email && !$mobile){
			$this->error(P_Lang('账号/手机号/邮箱至少要求一项不能为空'));
		}
		
		if($user){
			$safelist = array("'",'"','/','\\',';','&',')','(');
			foreach($safelist as $key=>$value){
				if($user && strpos($user,$value) !== false){
					$this->error(P_Lang('用户账号不允许包含字符串：{string}',array('string'=>$value)));
				}
			}
			$chk = $this->model('user')->chk_name($user);
			if($chk){
				$this->error(P_Lang('用户账号已存用'));
			}
		}
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->error(P_Lang('邮箱不合法'));
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->error(P_Lang('邮箱已注册'));
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->error(P_Lang('手机号不合法'));
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->error(P_Lang('手机号已注册'));
			}
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
		
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('图形验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('图形验证码填写不正确'));
			}
			$this->session->unassign('vcode');
		}

		$user_status = $group_rs['register_status'] == 1 ? 1 : 0;
		$relaction_id = 0;
		$code = $this->get("_vcode");
		if(in_array($group_rs['register_status'],array('mobile','email'))){
			if(!$code){
				$this->error(P_Lang('验证码不能为空'));
			}
			if($group_rs['register_status'] == 'email' && $email){
				$chkemail = $this->session->val('vcode2email');
				if(!$chkemail || $chkemail != $email){
					$this->error(P_Lang('邮箱与验证码邮箱不一致'));
				}
			}
			if($group_rs['register_status'] == 'mobile' && $mobile){
				$chkmobile = $this->session->val('vcode2mobile');
				if(!$chkmobile || $chkmobile != $mobile){
					$this->error(P_Lang('手机号与验证码手机号不一致'));
				}
			}
			$tmp = $this->model('vcode')->check($code);
			if(!$tmp){
				$this->error($this->model('vcode')->error_info());
			}
			$user_status = 1;
		}elseif($group_rs['register_status'] == 'code'){
			
			if(!$code){
				$this->error(P_Lang('邀请码不能为空'));
			}
			$tmp = $this->model('user')->get_one($code,'code',false,false);
			if(!$tmp){
				$this->error(P_Lang('邀请码不存在，请检查'));
			}
			$user_status = 1;
			$relaction_id = $tmp['id'];
		}else{
			if($code){
				$tmp = $this->model('user')->get_one($code,'code',false,false);
				if($tmp){
					$relaction_id = $tmp['id'];
				}
			}
		}
		
		$array = array();
		$array["user"] = $user ? $user : ($mobile ? $mobile : $email);
		$array["pass"] = password_create($newpass);
		$array['email'] = $email;
		$array['mobile'] = $mobile;
		$array["group_id"] = $group_id;
		$array["status"] = $user_status;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->error(P_Lang('注册失败，请联系管理员'));
		}
		//生成自己的邀请码
		if($group_rs['register_status'] == 'code'){
			$mycode = 'U'.$uid;
			$this->model('user')->save(array('code'=>$mycode),$uid);
		}
		if(!$relaction_id && $this->session->val('introducer')){
			$relaction_id = $this->session->val('introducer');
		}
		if($relaction_id){
			$this->model('user')->save_relation($uid,$relaction_id);
		}
		$extlist = $this->model('user')->fields_all();
		$ext = array();
		$ext["id"] = $uid;
		if($extlist){
			foreach($extlist as $key=>$value){
				$ext[$value["identifier"]] = ext_value($value);
			}
		}
		$this->model('user')->save_ext($ext);
		$code = $this->get('code');//推荐码
		if($code && !$relaction_id){
			$tmp = $this->model('user')->get_one($code,'code',false,false);
			if($tmp){
				$this->model('user')->save_relation($uid,$tmp['id']);
			}
		}
		if(!$user_status){
			$this->success($uid);
		}
		$this->model('wealth')->register($uid,P_Lang('用户注册'));
		//用户自动登录
		$autologin = $this->get('_login','int');
		if($autologin){
			$array = $this->model('user')->login($uid,false);
			if($array){
				$array['token'] = $this->model('token')->create($uid);
			}
			$this->success($array);
		}
		$this->success($uid);
	}

	/**
	 * 用户账号激活
	**/
	public function active_f()
	{
		$user = $this->get('user');
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if(!$user && !$mobile && !$email){
			$this->error(P_Lang('账号/邮箱/手机号至少有一个不能为空'));
		}
		if($user){
			$rs = $this->model('user')->get_one($user,'user',false,false);
			if(!$rs){
				$this->error(P_Lang('用户信息不存在'));
			}
		}
		if(!$user && $mobile){
			$rs = $this->model('user')->get_one($mobile,'mobile',false,false);
			if(!$rs){
				$this->error(P_Lang('用户信息不存在'));
			}
		}
		if(!$user && !$mobile && $email){
			$rs = $this->model('user')->get_one($mobile,'email',false,false);
			if(!$rs){
				$this->error(P_Lang('用户信息不存在'));
			}
		}
		if($rs['status'] == 2){
			$this->error(P_Lang('账号已被管理员锁定，请联系客服'));
		}
		if($rs['status']){
			$this->error(P_Lang('账号已经激活，不能重复操作'));
		}
		if(!$rs['code']){
			$this->error(P_Lang('用户激活码已经失效，请联系客服'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('请填写您收到的激活码'));
		}
		if($rs['code'] != $code){
			$this->error(P_Lang('激活码填写不正确'));
		}
		$newpass = $this->get('newpass');
		$chkpass = $this->get('chkpass');
		if(!$newpass){
			$this->error(P_Lang('密码不能为空'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('两次输入的密码不一致'));
		}
		$array = array();
		$array["pass"] = password_create($newpass);
		$array["status"] = 1;
		$array['code'] = '';
		$this->model('user')->save($array,$rs['id']);
		$this->success();
	}
}