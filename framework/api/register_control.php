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
	function __construct()
	{
		parent::control();
	}

	//检测会员是否存在
	function check_user_f()
	{
		$user = $this->get("user");
		if(!$user)
		{
			$this->json(P_Lang('账号不能为空'));
		}
		//检测账号是否符合要求
		$safelist = array("'",'"','/','\\',';','.',')','(');
		foreach($safelist as $key=>$value)
		{
			if(strpos($user,$value) !== false)
			{
				$this->json(P_Lang('会员账号不允许包含字符串：'.$value.'，请检查'));
			}
		}
		$rs = $this->model('user')->chk_name($user);
		if($rs)
		{
			$this->json(P_Lang('会员账号已经存在'));
		}
		$this->json(P_Lang('账号可以使用'),true);
	}

	//存储注册信息
	function save_f()
	{
		//判断是否是会员
		if($_SESSION['user_id'])
		{
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->config['is_vcode'] && function_exists('imagecreate'))
		{
			$code = $this->get('_chkcode');
			if(!$code)
			{
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode_api'])
			{
				$this->json(P_Lang('验证码填写不正确'));
			}
			unset($_SESSION['vcode_api']);
		}
		//检测会员账号
		$user = $this->get("user");
		if(!$user)
		{
			$this->json('账号不能为空');
		}
		//检测账号是否符合要求
		$safelist = array("'",'"','/','\\',';','.',')','(');
		foreach($safelist as $key=>$value)
		{
			if(strpos($user,$value) !== false)
			{
				$this->json(P_Lang('会员账号不允许包含字符串：'.$value.'，请检查'));
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk)
		{
			$this->json(P_Lang('会员账号已经存在，请选择其他账号'));
		}
		

		//检测密码是否符合要求
		$newpass = $this->get('newpass');
		if(!$newpass)
		{
			$this->json(P_Lang('密码不能为空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass)
		{
			$this->json(P_Lang('确认密码不能为空'));
		}
		if($newpass != $chkpass)
		{
			$this->json(P_Lang('两次输入的密码不一致'));
		}
		//验证邮箱
		$email = $this->get('email');
		if(!$email)
		{
			$this->json(P_Lang('邮箱不能为空'));
		}
		if(!phpok_check_email($email))
		{
			$this->json(P_Lang('邮箱不合法'));
		}
		$chk = $this->model('user')->user_email($email);
		if($chk)
		{
			$this->json(P_Lang('该邮箱已被注册，不能重复注册'));
		}

		//存储主表数据
		$array = array();
		$array["user"] = $user;
		$array["pass"] = password_create($newpass);
		$array['email'] = $email;
		$array['mobile'] = $this->get('mobile');
		$group_id = $this->get("group_id","int");
		if($group_id)
		{
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status'])
			{
				$group_id = 0;
			}
		}
		if(!$group_id)
		{
			$group_rs = $this->model('usergroup')->get_default();
			if(!$group_rs || !$group_rs["status"])
			{
				$this->json(P_Lang('注册失败，请联系管理员开放注册用户组权限'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id)
		{
			$this->json(P_Lang('注册失败，请联系管理员开放注册用户组权限'));
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"])
		{
			$this->json(P_Lang('注册失败，请联系管理员开放注册用户组权限'));
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] == '1' ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid)
		{
			$this->json(P_Lang('注册失败，数据写入失败，请联系管理员'));
		}
		//增加一个记录，减少用户刷新式注册
		$_SESSION['register_count'] = $_SESSION['register_count'] ? $_SESSION['register_count'] + 1 : 1;

		//更新会员表扩展字段内容
		$extlist = $this->model('user')->fields_all();
		$ext = array();
		$ext["id"] = $uid;
		if($extlist)
		{
			foreach($extlist AS $key=>$value)
			{
				$ext[$value["identifier"]] = ext_value($value);
			}
		}
		$this->model('user')->save_ext($ext);
		if($array['status'])
		{
			$rs = $this->model('user')->get_one($uid);
			$_SESSION["user_id"] = $rs['id'];
			$_SESSION["user_rs"] = $rs;
			$_SESSION["user_name"] = $rs["user"];
			$this->json(P_Lang('会员注册成功'),true);
		}
		//未设置审核，直接中止，弹出提示
		if(!$group_rs["tbl_id"] || !$group_rs['register_status'])
		{
			$this->json(P_Lang('会员注册成功'),true);
		}
		//判断项目是否有绑定模块，没有模块的禁止使用
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module'])
		{
			$this->json(true);
		}
		//判断是否有验证串，没有验证串的，停止验证
		$code = $this->get('_code');
		if(!$code)
		{
			$this->json(P_Lang('会员注册成功'),true);
		}
		//取得内容信息
		$info = $this->model('list')->get_one_condition("l.title='".$code."'",$project['module']);
		if($info)
		{
			$ext = array('site_id'=>$info['site_id'],'project_id'=>$info['project_id']);
			$ext['account'] = $user;
			$this->model('list')->update_ext($ext,$project['module'],$info['id']);
			$this->model('user')->set_status($uid,1);
			$this->model('user')->update_session($uid);
		}
		$this->json(true);
	}

	//检测邀请码是否有效
	public function code_f()
	{
		//判断是否是会员
		if($_SESSION['user_id'])
		{
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->config['is_vcode'] && function_exists('imagecreate'))
		{
			$code = $this->get('_chkcode');
			if(!$code)
			{
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $_SESSION['vcode_api'])
			{
				$this->json(P_Lang('验证码填写不正确'));
			}
			unset($_SESSION['vcode_api']);
		}
		$code = $this->get('_code');
		if(!$code)
		{
			$this->json(P_Lang('邀请码不能为空'));
		}
		//判断是否存在
		$group_id = $this->get('group_id','int');
		if($group_id)
		{
			$group_rs = $this->model('usergroup')->get_one($group_id);
			if(!$group_rs || !$group_rs['status']) $group_id = 0;
		}
		if(!$group_id)
		{
			$group_rs = $this->model('usergroup')->get_default(1);
			if(!$group_rs)
			{
				$this->json(P_Lang('注册异常，请联系管理员开放注册用户组权限'));
			}
			$group_id = $group_rs['id'];
		}
		if(!$group_rs['register_status'] || $group_rs['register_status'] == '1')
		{
			$this->json(P_Lang('该组不需要启用邀请码功能'));
		}
		if(!$group_rs['tbl_id'])
		{
			$this->json(P_Lang('未分配相应的验证组功能'));
		}
		$project = $this->model("project")->get_one($group_rs["tbl_id"],false);
		if(!$project['module'])
		{
			$this->json(P_Lang('验证库未绑定相应的模块，请检查'));
		}
		$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$project['module']);
		if(!$chk_rs)
		{
			$this->json(P_Lang('邀请码不存在'));
		}
		if($chk_rs && $chk_rs["account"])
		{
			$this->json(P_Lang('邀请码已被使用'));
		}
		if(!$chk_rs["status"])
		{
			$this->json(P_Lang('邀请码未启用，您可以联系管理员启用'));
		}
		$url = $this->url('register','','_code='.rawurlencode($code).'&group_id='.$group_id,'www');
		$this->json($url,true);		
	}
}
?>