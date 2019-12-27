<?php
/**
 * 会员注册
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月25日
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
	 * 注册页面，包含注册验证页，使用到模板：register_check_项目ID
	 * @参数 _back 返回上一页
	 * @参数 _code 验证码
	 * @参数 email 邮箱
	**/
	public function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已登录，不用注册'),$_back);
		}
		$this->assign('_back',$_back);
		if(!$this->site['register_status']){
			$tips = $this->site["register_close"] ? $this->site["register_close"] : P_Lang('系统暂停会员注册，请联系站点管理员');
			$this->error($tips,$_back);
		}
		//取得开放的会员组信息
		$grouplist = $this->model("usergroup")->opened_grouplist();
		if(!$grouplist){
			$this->error(P_Lang('未找到有效的会员组信息'),$_back,10);
		}
		$this->assign("grouplist",$grouplist);
		$gid = $this->get("group_id","int");
		if($gid){
			$group_rs = $this->model("usergroup")->get_one($gid);
			if(!$group_rs || !$group_rs["status"]){
				$gid = 0;
			}
		}
		if(!$gid){
			if(count($grouplist) == 1){
				$group_rs = current($grouplist);
				$gid = $group_rs['id'];
			}else{
				foreach($grouplist as $key=>$value){
					if($value["is_default"]){
						$gid = $value["id"];
						$group_rs = $value;
					}
				}
			}
		}
		//判断是否使用验证码注册
		$this->assign("group_id",$gid);
		$this->assign("group_rs",$group_rs);
		//取得当前组的扩展字段
		$ext_list = $this->model("user")->fields_all("is_front=1");
		$extlist = false;
		if(!$ext_list){
			$ext_list = array();
		}
		foreach($ext_list as $key=>$value){
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext as $k=>$v){
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			if(!$group_rs['fields'] || ($group_rs['fields'] && in_array($value['identifier'],explode(",",$group_rs['fields'])))){
				$extlist[] = $this->lib('form')->format($value);
			}
		}
		$this->assign("extlist",$extlist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register';
		}
		if($group_rs['register_status'] && $group_rs['register_status'] != '1'){
			$tplfile = 'register_'.$group_rs['register_status'];
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','register'));
		$this->view($tplfile);
	}

	/**
	 * 友情提示页
	**/
	public function tip_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register_tip';
		}
		$this->view($tplfile);
	}

	/**
	 * 注册成功页
	**/
	public function success_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register_success';
		}
		$this->view($tplfile);
	}

	/**
	 * 注册提交成功信息
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不能执行这个操作'));
		}
		$group_id = $this->get("group_id","int");
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
			if(!$group_rs['is_open'] && !$group_rs['is_default']){
				$this->error(P_Lang('指定的会员组没有开放申请，请联系管理员'));
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
					$this->error(P_Lang('会员账号不允许包含字符串：{string}',array('string'=>$value)));
				}
			}
			$chk = $this->model('user')->chk_name($user);
			if($chk){
				$this->error(P_Lang('会员账号已存用'));
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
		$user_status = 0;
		$relaction_id = 0;
		$code = $this->get('_vcode');
		if(!$code && in_array($group_rs['register_status'],array('mobile','email','code'))){
			$this->error(P_Lang('验证码不能为空'));
		}
		if($code && in_array($group_rs['register_status'],array('mobile','email'))){
			$tmp = $this->model('vcode')->check($code);
			if(!$tmp){
				$this->error($this->model('vcode')->error_info());
			}
			$user_status = 1;
		}
		if(!$code && $group_rs['register_status'] == 'code'){
			$this->error(P_Lang('邀请码不能为空'));
		}
		//检测推荐码是否存在
		if($code && $group_rs['register_status'] == 'code'){
			$tmp = $this->model('user')->get_one($code,'code',false,false);
			if(!$tmp){
				$this->error(P_Lang('邀请码不存在，请检查'));
			}
			$user_status = 1;
			$relaction_id = $tmp['id'];
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
				$this->error(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode');
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
		if(!$user_status){
			$this->success($uid);
		}
		$this->model('wealth')->register($uid,P_Lang('会员注册'));
		//会员自动登录
		$autologin = $this->get('_login','int');
		if($autologin){
			$this->session->assign('user_id',$uid);
			$this->session->assign('user_gid',$group_id);
			$this->session->assign('user_name',$array["user"]);
		}
		$this->success($uid);
	}

	public function active_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->get_one($id,'id',false,false);
			if(!$rs){
				$this->error(P_Lang('会员信息不存在'));
			}
			if($rs['status'] == 2){
				$this->error(P_Lang('账号已被管理员锁定，请联系客服'));
			}
			if($rs['status']){
				$this->error(P_Lang('账号已经激活，不能重复操作'));
			}
			if(!$rs['code']){
				$this->error(P_Lang('激活码不存在或已经失效，请联系客服'));
			}
			$this->assign('rs',$rs);
		}
		$code = $this->get('code');
		$this->assign('code',$code);
		$this->view('register_active');
	}
}