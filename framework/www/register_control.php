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
				foreach($grouplist AS $key=>$value){
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
		if($group_rs["register_status"] && $group_rs["register_status"] != "1"){
			if(!$group_rs['tbl_id']){
				$this->error(P_Lang('未绑定验证项目'),$_back);
			}
			$p_rs = $this->model("project")->get_one($group_rs["tbl_id"],false);
			if(!$p_rs['module']){
				$this->error(P_Lang('绑定的项目中没有关联模块'),$_back);
			}
			$code = $this->get('_code');
			if(!$code){
				$tplfile = 'register_check_'.$group_rs['register_status'];
				if(!$this->tpl->check($tplfile)){
					$tplfile = 'register_chkcode';
					if(!$this->tpl->check($tplfile)){
						$this->error(P_Lang('绑定验证串的模板不存，请检查'));
					}
				}
				$this->view($tplfile);
				exit;
			}
			$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$p_rs['module']);
			if(!$chk_rs){
				$this->error(P_Lang("验证码不正确，请检查"),$this->url("register"));
			}
			if($chk_rs && $chk_rs["account"]){
				$this->error(P_Lang("验证码已使用过，请填写新的验证码"),$this->url("register"));
			}
			if(!$chk_rs["status"]){
				$this->error(P_Lang("验证码未启用"),$this->url("register"));
			}
			if(($chk_rs['dateline'] + 86400) < $this->time){
				error(P_Lang('验证码已过期'),$this->url('register'));
			}
			$email = $this->get('email');
			if($email){
				$this->assign('account',$email);
				$this->assign('email',$email);
			}
			$this->assign("code",$code);
		}
		//取得当前组的扩展字段
		$ext_list = $this->model("user")->fields_all("is_edit=1");
		$extlist = false;
		if(!$ext_list){
			$ext_list = array();
		}
		foreach($ext_list AS $key=>$value){
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
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
		$this->assign('is_vcode',$this->model('site')->vcode('system','register'));
		$this->view($tplfile);
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
	 * @更新时间 2016年08月01日
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站会员，不能执行这个操作'),$this->url);
		}
		$errurl = $this->url('register');
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('验证码不能为空'),$errurl);
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('验证码填写不正确'),$errurl);
			}
			$this->session->unassign('vcode');
		}
		//检测会员账号
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('账号不能为空'),$errurl);
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->error(P_Lang('会员账号不允许包含字符串：{string}',array('string'=>$value)),$errurl);
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk){
			$this->error(P_Lang('会员账号已存用'),$errurl);
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->error(P_Lang('密码不能为空'),$errurl);
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->error(P_Lang('确认密码不能为空'),$errurl);
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('两次输入的密码不一致'),$errurl);
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->error(P_Lang('邮箱不合法'),$errurl);
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->error(P_Lang('邮箱已注册'),$errurl);
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->error(P_Lang('手机号不合法'),$errurl);
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->error(P_Lang('手机号已注册'),$errurl);
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
				$this->error(P_Lang('注册失败，网站未开放注册权限'),$errurl);
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->error(P_Lang('注册失败，网站未开放注册权限'),$errurl);
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"]){
			$this->error(P_Lang('注册失败，网站未开放注册权限'),$errurl);
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->error(P_Lang('注册失败，请联系管理员'),$errurl);
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
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//注册审核通过后赠送积分
			$this->model('wealth')->register($uid,P_Lang('会员注册'));
			$this->success(P_Lang('注册成功，已自动登录，请稍候…'),$this->url);
		}
		if(!$group_rs["tbl_id"] && !$group_rs['register_status']){
			$this->success(P_Lang('注册成功，等待管理员验证'),$this->url);
		}
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module']){
			$this->success(P_Lang('注册成功，等待管理员验证'),$this->url);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->success(P_Lang('注册成功，等待管理员验证'),$this->url);
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
			$this->success(P_Lang('注册成功，已自动登录，请稍候…'),$this->url);
		}
		$this->success(P_Lang('注册成功，等待管理员验证'),$this->url);
	}
}