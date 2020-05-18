<?php
/**
 * 会员中心数据存储
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月27日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class usercp_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}
	
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$user = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$user){
			$this->error(P_Lang('会员信息不存在'));
		}
		if(!$user['status']){
			$this->error(P_Lang('会员信息未审核通过'));
		}
		if($user['status'] == 2){
			$this->error(P_Lang('会员已被禁用，请联系管理员'));
		}
		if(isset($user['pass'])){
			unset($user['pass']);
		}
		$this->success($user);
	}

	/**
	 * 存储个人数据
	**/
	public function info_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$group_rs = $this->model('usergroup')->group_rs($this->session->val('user_id'));
		if(!$group_rs){
			$this->error(P_Lang('会员组不存在'));
		}
		$condition = 'is_front=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$ext = array();
			foreach($ext_list as $key=>$value){
				$ext[$value['identifier']] = $this->lib('form')->get($value);
			}
			if($ext && count($ext)>0){
				$this->model('user')->update_ext($ext,$this->session->val('user_id'));
			}
		}
		$this->success();
	}

	/**
	 * 更新会员头像
	**/
	public function avatar_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$type = $this->get('type');
		if($type == 'base64'){
			$data = $this->get('data');
			if(!$data){
				$this->error(P_Lang('图片内容不能为空'));
			}
			if(strpos($data,',') === false){
				$this->error(P_Lang('附片格式不正确'));
			}
			$tmp = explode(",",$data);
			$tmpinfo = substr($data,strlen($tmp[0]));
			$content = base64_decode($tmpinfo);
			if($content == $tmpinfo){
				$this->error(P_Lang('不是合法的图片文件'));
			}
			$info = explode(";",$tmp[0]);
			$ext = 'png';
			if($info[0]){
				$tmp = explode("/",$info[0]);
				if($tmp[1]){
					$ext = $tmp[1];
				}
			}
			if(!in_array($ext,array('jpg','png','gif','jpeg'))){
				$this->error(P_Lang('上传的文件格式不附合系统要求'));
			}
			if($ext == 'jpeg'){
				$ext = 'jpg';
			}
			$save_pic = 'res/user/'.$this->session->val('user_id').'.'.$ext;
			$this->lib('file')->rm($this->dir_root.$save_pic);
			$this->lib('file')->save_pic($content,$this->dir_root.$save_pic);
			//生成正方式
			$this->lib('gd')->thumb($this->dir_root.$save_pic,$this->session->val('user_id'),100,100);
			$this->lib('file')->mv('res/user/_'.$this->session->val('user_id').'.'.$ext,$save_pic);
			$this->model('user')->update_avatar($save_pic,$this->session->val('user_id'));
			$this->success();
		}
		$data = $this->get('data');
		if(!$data){
			$this->error(P_Lang('头像图片地址不能为空'));
		}
		$pInfo = pathinfo($data);
		$fileType = strtolower($pInfo['extension']);
		if(!$fileType || !in_array($fileType,array('jpg','gif','png','jpeg'))){
			$this->error(P_Lang('头像图片仅支持jpg,gif,png,jpeg'));
		}
		if(!file_exists($this->dir_root.$data)){
			$this->error(P_Lang('头像文件不存在'));
		}
		$this->model('user')->update_avatar($data,$this->session->val('user_id'));
		$this->success();
	}

	/**
	 * 更新会员密码功能
	**/
	public function passwd_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$user = $this->model('user')->get_one($this->session->val('user_id'));
		if($user['pass']){
			$oldpass = $this->get("oldpass");
			if(!$oldpass){
				$this->error(P_Lang('旧密码不能为空'));
			}
			if(!password_check($oldpass,$user["pass"])){
				$this->error(P_Lang('旧密码输入错误'));
			}
		}
		$newpass = $this->get("newpass");
		$chkpass = $this->get("chkpass");
		if(!$newpass || !$chkpass){
			$this->error(P_Lang('新密码不能为空'));
		}
		if(strlen($newpass) < 6){
			$this->error(P_Lang('密码不符合要求，密码长度不能小于6位'));
		}
		if(strlen($newpass) > 20){
			$this->error(P_Lang('密码不符合要求，密码长度不能超过20位'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('新旧密码不一致'));
		}
		if($oldpass && $oldpass == $newpass){
			$this->error(P_Lang('新旧密码不能一样'));
		}
		$password = password_create($newpass);
		$this->model('user')->update_password($password,$this->session->val('user_id'));
		$this->success();
	}

	/**
	 * 更新会员手机
	**/
	public function mobile_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$pass = $this->get('pass');
		if(!$pass){
			$this->error(P_Lang('密码不能为空'));
		}
		$me = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$me){
			$this->error(P_Lang('会员信息不存在'));
		}
		if(!$me['status'] || $me['status'] == 2){
			$this->error(P_Lang('会员未审核或已销定，请联系管理员'));
		}
		if(!$me['pass']){
			$this->error(P_Lang('您还未设置密码，请先设置密码'));
		}
		if(!password_check($pass,$me["pass"])){
			$this->error(P_Lang('密码输入错误'));
		}
		$newmobile = $this->get("mobile");
		if(!$newmobile){
			$this->error(P_Lang('新手机号码不能为空'));
		}
		if($user['mobile'] == $newmobile){
			$this->error(P_Lang('新旧手机号码不能一样'));
		}
		$uid = $this->model('user')->uid_from_mobile($newmobile,$this->session->val('user_id'));
		if($uid){
			$this->error(P_Lang('手机号码已被使用'));
		}
		$server = $this->model('gateway')->get_default('sms');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->error(P_Lang('验证码不能为空'));
			}
			$check = $this->model('vcode')->check($chkcode);
			if(!$check){
				$this->error($this->model('vcode')->error_info());
			}
			$this->model('vcode')->delete();
		}
		$this->model('user')->update_mobile($newmobile,$this->session->val('user_id'));
		$this->success();
	}

	/**
	 * 更新会员邮箱
	**/
	public function email_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$pass = $this->get('pass');
		if(!$pass){
			$this->error(P_Lang('密码不能为空'));
		}
		$email = $this->get("email");
		if(!$email){
			$this->error(P_Lang('新邮箱不能为空'));
		}
		//判断邮箱是否合法
		$chk = $this->lib('common')->email_check($email);
		if(!$chk){
			$this->error(P_Lang('邮箱格式不正确，请重新填写'));
		}
		$user = $this->model('user')->get_one($this->session->val('user_id'));
		if($user['email'] == $email){
			$this->error(P_Lang('新旧邮箱不能一样'));
		}
		$chk = $this->model('user')->uid_from_email($email,$this->session->val('user_id'));
		if($chk){
			$this->error(P_Lang('邮箱已被使用，请更换其他邮箱'));
		}
		$server = $this->model('gateway')->get_default('email');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->error(P_Lang('验证码不能为空'));
			}
			$check = $this->model('vcode')->check($chkcode);
			if(!$check){
				$this->error($this->model('vcode')->error_info());
			}
			$this->model('vcode')->delete();
		}
		$this->model('user')->save(array('email'=>$email),$this->session->val('user_id'));
		$this->success();
	}

	/**
	 * 变更个人信息，通过fields获取要变更的扩展参数信息，仅用于保存会员扩展表里字符类型
	 * @参数 fields 要更新的变量
	**/
	public function save_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		$fields = $this->get('fields');
		if(!$fields){
			$this->error(P_Lang('未指定要修改的字段'));
		}
		$list = explode(",",$fields);
		$flist = $this->model('user')->fields_all("is_front=1");
		if(!$flist){
			$this->error(P_Lang('没有可编辑的字段'));
		}
		$idlist = array();
		foreach($flist as $key=>$value){
			$idlist[] = $value['identifier'];
		}
		$array = array();
		foreach($list as $key=>$value){
			if(!in_array($value,$idlist)){
				continue;
			}
			$val = $this->get($value);
			$array[$value] = $val;
		}
		if($array && count($array)>0){
			$this->model("user")->update_ext($array,$this->session->val('user_id'));
			$this->success();
		}
		$this->error(P_Lang('没有接收到参数及值'));
	}
	
	//获取推荐人信息
	public function relation_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能查看我的推荐用户'));
		}
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		}
		$offset = ($pageid-1) * $psize;
		$total = $this->model('user')->count_relation($this->session->val('user_id'));
		if(!$total){
			$this->error(P_Lang('没有推荐人'));
		}
		$data = array('total'=>$total,'pageid'=>$pageid,'psize'=>$psize);
		$rslist = $this->model('user')->list_relation($this->session->val('user_id'),$offset,$psize);		
		if($rslist){
			$data['rslist'] = $rslist;
		}
		$this->success($data);
	}
}