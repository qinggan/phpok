<?php
/***********************************************************
	Filename: {phpok}/api/register_control.php
	Note	: 注册API接口
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年10月11日 05时41分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class register_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function check_user_f()
	{
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('账号不能为空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('会员账号不允许包含字符串：').$value);
			}
		}
		$rs = $this->model('user')->chk_name($user);
		if($rs){
			$this->json(P_Lang('会员账号已存用'));
		}
		$this->json(P_Lang('账号可以使用'),true);
	}

	public function save_f()
	{
		//判断是否是会员
		if($_SESSION['user_id']){
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->config['is_vcode'] && function_exists('imagecreate')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode']){
				$this->json(P_Lang('验证码填写不正确'));
			}
			unset($_SESSION['vcode']);
		}
		//检测会员账号
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('账号不能为空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('会员账号不允许包含字符串：').$value);
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk){
			$this->json(P_Lang('会员账号已存用'));
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->json(P_Lang('密码不能为空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->json(P_Lang('确认密码不能为空'));
		}
		if($newpass != $chkpass){
			$this->json(P_Lang('两次输入的密码不一致'));
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->json(P_Lang('邮箱不合法'));
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->json(P_Lang('邮箱已注册'));
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->json(P_Lang('手机号不合法'));
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->json(P_Lang('手机号已注册'));
			}
		}
		
		$array = array();
		$array["user"] = $user;
		$array["pass"] = password_create($newpass);
		$array['email'] = $email;
		$array['mobile'] = $mobile;
		$group_id = $this->get("group_id","int");
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
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
			$this->json(P_Lang('注册失败，网站未开放注册权限'));
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"]){
			$this->json(P_Lang('注册失败，网站未开放注册权限'));
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] == '1' ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->json(P_Lang('注册失败，请联系管理员'));
		}
		if($uid){
			//保存用户与用户的关系
			if($_SESSION['introducer']){
				$this->model('user')->save_relation($uid,$_SESSION['introducer']);
			}
			$this->model('wealth')->wealth_autosave($uid,P_Lang('会员注册'));
		}
		$extlist = $this->model('user')->fields_all();
		$ext = array();
		$ext["id"] = $uid;
		if($extlist){
			foreach($extlist AS $key=>$value){
				$ext[$value["identifier"]] = ext_value($value);
			}
		}
		$this->model('user')->save_ext($ext);
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$_SESSION["user_id"] = $rs['id'];
			$_SESSION["user_gid"] = $rs['group_id'];
			$_SESSION["user_name"] = $rs["user"];
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		if(!$group_rs["tbl_id"] && !$group_rs['register_status']){
			$this->json(P_Lang('注册成功，等待管理员验证'),true);
		}
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module']){
			$this->json(P_Lang('注册成功，等待管理员验证'),true);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->json(P_Lang('注册成功，等待管理员验证'),true);
		}
		$info = $this->model('list')->get_one_condition("l.title='".$code."'",$project['module']);
		if($info){
			$ext = array('site_id'=>$info['site_id'],'project_id'=>$info['project_id']);
			$ext['account'] = $user;
			$this->model('list')->update_ext($ext,$project['module'],$info['id']);
			$this->model('user')->set_status($uid,1);
			$this->model('user')->update_session($uid);
			$rs = $this->model('user')->get_one($uid);
			$_SESSION["user_id"] = $rs['id'];
			$_SESSION["user_gid"] = $rs['group_id'];
			$_SESSION["user_name"] = $rs["user"];
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		$this->json(P_Lang('注册成功，等待管理员验证'),true);
	}

	public function code_f()
	{
		if($_SESSION['user_id']){
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->config['is_vcode'] && function_exists('imagecreate')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode']){
				$this->json(P_Lang('验证码填写不正确'));
			}
			unset($_SESSION['vcode']);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->json(P_Lang('邀请码不能为空'));
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$group_rs = $this->model('usergroup')->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default(1);
			if(!$group_rs){
				$this->json(P_Lang('注册失败，网站未开放注册权限'));
			}
			$group_id = $group_rs['id'];
		}
		if(!$group_rs['register_status'] || $group_rs['register_status'] == '1'){
			$this->json(P_Lang('该组不需要启用邀请码功能'));
		}
		if(!$group_rs['tbl_id']){
			$this->json(P_Lang('未分配相应的验证组功能'));
		}
		$project = $this->model("project")->get_one($group_rs["tbl_id"],false);
		if(!$project['module']){
			$this->json(P_Lang('验证库未绑定相应的模块'));
		}
		$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$project['module']);
		if(!$chk_rs){
			$this->json(P_Lang('邀请码不存在'));
		}
		if($chk_rs && $chk_rs["account"]){
			$this->json(P_Lang('邀请码已被使用'));
		}
		if(!$chk_rs["status"]){
			$this->json(P_Lang('邀请码未启用，您可以联系管理员启用'));
		}
		$url = $this->url('register','','_code='.rawurlencode($code).'&group_id='.$group_id,'www');
		$this->json($url,true);		
	}
}
?>