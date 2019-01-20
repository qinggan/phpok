<?php
/**
 * 注册接口API
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
	 * @返回 json字串
	**/
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

	/**
	 * 保存注册信息
	 * @参数 _chkcode 验证码
	 * @参数 user 账号
	 * @参数 newpass 密码
	 * @参数 chkpass 确认密码
	 * @参数 email 邮箱
	 * @参数 mobile 手机号
	 * @参数 group_id 用户组ID
	 * @参数 _code 注册推广码
	 * @返回 Json字串
	 * @更新时间 2016年07月30日
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode');
		}
		//检测会员账号
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('账号不能为空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('会员账号不允许包含字符串：{string}',array('string'=>$value)));
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
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->json(P_Lang('注册失败，请联系管理员'));
		}
		if($uid){
			if($this->session->val('introducer')){
				$this->model('user')->save_relation($uid,$this->session->val('introducer'));
			}
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
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//注册审核通过后赠送积分
			$this->model('wealth')->register($uid,P_Lang('会员注册'));
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//注册审核通过后赠送积分
			$this->model('wealth')->register($uid,P_Lang('会员注册'));
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		$this->json(P_Lang('注册成功，等待管理员验证'),true);
	}

	/**
	 * 注册提交成功信息
	 * @参数 
	 * @参数 
	 * @参数 
	**/
	public function ok_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->model('site')->vcode('system','register')){
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
		//检测会员账号
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('账号不能为空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('会员账号不允许包含字符串：{string}',array('string'=>$value)));
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
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->json(P_Lang('注册失败，请联系管理员'));
		}
		if($uid){
			if($this->session->val('introducer')){
				$this->model('user')->save_relation($uid,$this->session->val('introducer'));
			}
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
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//注册审核通过后赠送积分
			$this->model('wealth')->register($uid,P_Lang('会员注册'));
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//注册审核通过后赠送积分
			$this->model('wealth')->register($uid,P_Lang('会员注册'));
			$this->json(P_Lang('注册成功，已自动登录，请稍候…'),true);
		}
		$this->json(P_Lang('注册成功，等待管理员验证'),true);
	}

    /**
     *发送短信验证码
    **/
    public function sms_f()
    {
        $smstpl = $this->site['login_type_sms'];
        if(!$smstpl){
	        $this->error(P_Lang('短信验证码模板未指定'));
        }
        $mobile = $this->get('mobile');
        if(!$mobile){
            $this->error(P_Lang('手机号不能为空'));
        }
        if(!$this->lib('common')->tel_check($mobile,'mobile')){
            $this->error(P_Lang('手机号不符合格式要求'));
        }
        $chk = $this->model('user')->user_mobile($mobile);
        if($chk){
            $this->json(P_Lang('手机号已被使用，请更换其他手机号'));
        }
        $code = $this->session->val('register_code');
        if($code){
            $time = $this->session->val('register_code_time');
            $chktime = $this->time - 60;
            if($time && $time > $chktime){
                $this->error(P_Lang('验证码已发送，请等待一分钟后再获取'));
            }
        }
        $this->gateway('type','sms');
        $this->gateway('param','default');
        if(!$this->gateway('check')){
            $this->error(P_Lang('网关参数信息未配置'));
        }
        $code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
        if(!$code){
            $this->error(P_Lang('网关配置错误，请联系工作人员'));
        }
        if($code['code']){
            foreach($code['code'] as $key=>$value){
                if($value['required'] && $value['required'] == 'true' && !$this->gateway['param']['ext'][$key]){
                    $this->error(P_Lang('网关配置不完整，请联系工作人员'));
                }
            }
        }
        $tpl = $this->model('email')->tpl($smstpl);
        if(!$tpl){
            $this->error(P_Lang('短信验证模板获取失败，请检查'));
        }
        if(!$tpl['content']){
            $this->error(P_Lang('短信模板内容为空，请联系管理员'));
        }
        $tplcontent = strip_tags($tpl['content']);
        if(!$tplcontent){
            $this->error(P_Lang('短信模板内容是空的，请联系管理员'));
        }
        $info = $this->lib("vcode")->word();
        $this->assign('code',$info);
        $this->assign('mobile',$mobile);
        $content = $this->fetch($tplcontent,'msg');
        $title = $this->fetch($tpl['title'],'msg');
        $this->session->assign('register_code',$info);
        $this->session->assign('register_code_time',$this->time);
        $this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
        $this->success();
    }

	/**
	 * 检测验证串是否正确，正确则跳转到注册页
	 * @参数 _chkcode 验证码，防止机器人注册
	 * @参数 _code 验证串，通过Email得到的验证串,24小时内有效
	 * @参数 group_id 会员组ID
	 * @返回 Json字串
	 * @更新时间 2016年07月30日
	**/
	public function code_f()
	{
		if($this->session->val('user_id')){
			$this->json(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode');
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