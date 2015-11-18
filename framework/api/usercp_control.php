<?php
/***********************************************************
	Filename: {phpok}/api/usercp_control.php
	Note	: 会员中心数据存储
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月5日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class usercp_control extends phpok_control
{
	private $u_id; //会员ID
	private $u_name; //会员名字
	private $is_client = false;//判断是否客户端
	public function __construct()
	{
		parent::control();
		$token = $this->get('token');
		if($token){
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id'] || !$info['user_name']){
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $info['user_id'];
			$this->u_name = $info['user_name'];
			$this->is_client = true;
		}else{
			if(!$_SESSION['user_id']){
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $_SESSION['user_id'];
			$this->u_name = $_SESSION['user_name'];
		}
	}

	//存储个人数据
	public function info_f()
	{
		$group_rs = $this->model('usergroup')->group_rs($this->u_id);
		if(!$group_rs){
			$this->json(P_Lang('会员组不存在'));
		}
		$condition = 'is_edit=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$ext = "";
			foreach($ext_list as $key=>$value){
				$ext[$value['identifier']] = $this->lib('form')->get($value);
			}
			if($ext){
				$this->model('user')->update_ext($ext,$this->u_id);
			}
		}
		if(!$this->is_client){
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	//更新会员头像
	public function avatar_f()
	{
		$data = $this->get('data');
		if(!$data){
			$this->json(P_Lang('头像图片地址不能为空'));
		}
		$pInfo = pathinfo($data);
		$fileType = strtolower($pInfo['extension']);
		if(!$fileType || !in_array($fileType,array('jpg','gif','png','jpeg'))){
			$this->json(P_Lang('头像图片仅支持jpg,gif,png,jpeg'));
		}
		if(!file_exists($this->dir_root.$data)){
			$this->json(P_Lang('头像文件不存在'));
		}
		$this->model('user')->update_avatar($data,$this->u_id);
		$this->json(true);
	}

	//更新会员密码功能
	public function passwd_f()
	{
		$oldpass = $this->get("oldpass");
		if(!$oldpass){
			$this->json(P_Lang('旧密码不能为空'));
		}
		$newpass = $this->get("newpass");
		$chkpass = $this->get("chkpass");
		if(!$newpass || !$chkpass){
			$this->json(P_Lang('新密码不能为空'));
		}
		if(strlen($newpass) < 6){
			$this->json(P_Lang('密码不符合要求，密码长度不能小于6位'));
		}
		if(strlen($newpass) > 20){
			$this->json(P_Lang('密码不符合要求，密码长度不能超过20位'));
		}
		if($newpass != $chkpass){
			$this->json(P_Lang('新旧密码不一致'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if(!password_check($oldpass,$user["pass"])){
			$this->json(P_Lang('旧密码输入错误'));
		}
		if($oldpass == $newpass){
			$this->json(P_Lang('新旧密码不能一样'));
		}
		$password = password_create($newpass);
		$this->model('user')->update_password($password,$this->u_id);
		if(!$this->is_client){
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	//更新会员手机
	public function mobile_f()
	{
		$pass = $this->get('pass');
		if(!$pass){
			$this->json(P_Lang('密码不能为空'));
		}
		$newmobile = $this->get("mobile");
		if(!$newmobile){
			$this->json(P_Lang('新手机号码不能为空'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		//密码验证
		if(!password_check($pass,$user['pass'])){
			$this->json(P_Lang('密码填写错误'));
		}
		if($user['mobile'] == $newmobile){
			$this->json(P_Lang('新旧手机号码不能一样'));
		}
		$uid = $this->model('user')->uid_from_mobile($newmobile,$this->u_id);
		if($uid){
			$this->json(P_Lang('手机号码已被使用'));
		}
		$server = $this->model('gateway')->get_default('sms');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->json(P_Lang('验证码不能为空'));
			}
			$tmpcode = $this->model('gateway')->read_temp($server['id'],$this->u_id);
			if($tmpcode != $chkcode){
				$this->json(P_Lang('验证码填写不正确'));
			}
			//删除临时数据
			$this->model('gateway')->delete_temp($server['id'],$this->u_id);
		}
		$this->model('user')->update_mobile($newmobile,$this->u_id);
		if(!$this->is_client){
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	public function smscode_f()
	{
		$server = $this->model('gateway')->get_default('sms');
		if(!$server){
			$this->json(P_Lang('未配置好短信发送网关，不支持此操作'));
		}
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->json(P_Lang('手机号不能为空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->json(P_Lang('手机号格式不正确'));
		}
		$chk = $this->model('user')->user_mobile($mobile,$this->u_id);
		if($chk){
			$this->json(P_Lang('手机号已被使用，请更换其他手机号'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if($mobile == $user['mobile']){
			$this->json(P_Lang('手机号与当前使用是一致的，不需要修改'));
		}
		$code = rand(1000,9999);
		$info = $this->model('email')->tpl('sms_code');
		if(!$info){
			$this->json(P_Lang('未设置手机验证码模板sms_code，请联系管理员'));
		}
		$this->assign('mobile',$mobile);
		$this->assign('code',$code);
		$this->assign('user',$user);
		$fullname = $user['fullname'] ? $user['fullname'] : $user['user'];
		$content = $this->fetch($info['content'],'content');
		$data = array('content'=>$content,'mobile'=>$mobile,'fullname'=>$fullname);
		$action = $this->model('gateway')->action($server,$data);
		if($action){
			$this->model('gateway')->save_temp($code,$server['id'],$this->u_id);
			$this->json(true);
		}
		$this->json(P_Lang('短信发送失败，请检查'));
	}
	
	//更新会员邮箱
	public function email_f()
	{
		$pass = $this->get('pass');
		if(!$pass){
			$this->json(P_Lang('密码不能为空'));
		}
		$email = $this->get("email");
		if(!$email){
			$this->json(P_Lang('新邮箱不能为空'));
		}
		//判断邮箱是否合法
		$chk = $this->lib('common')->email_check($email);
		if(!$chk){
			$this->json(P_Lang('邮箱格式不正确，请重新填写'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if($user['email'] == $email){
			$this->json(P_Lang('新旧邮箱不能一样'));
		}
		$chk = $this->model('user')->uid_from_email($email,$this->u_id);
		if($chk){
			$this->json(P_Lang('邮箱已被使用，请更换其他邮箱'));
		}
		$server = $this->model('gateway')->get_default('email');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->json(P_Lang('验证码不能为空'));
			}
			$tmpcode = $this->model('gateway')->read_temp($server['id'],$this->u_id);
			if($tmpcode != $chkcode){
				$this->json(P_Lang('验证码填写不正确'));
			}
			//删除临时数据
			$this->model('gateway')->delete_temp($server['id'],$this->u_id);
		}
		$this->model('user')->save(array('email'=>$email),$this->u_id);
		if(!$this->is_client){
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	public function emailcode_f()
	{
		$server = $this->model('gateway')->get_default('email');
		if(!$server){
			$this->json(P_Lang('未配置好邮件发送网关，不支持此操作'));
		}
		$email = $this->get('email');
		if(!$email){
			$this->json(P_Lang('邮箱不能为空'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->json(P_Lang('邮箱格式不正确'));
		}
		$chk = $this->model('user')->uid_from_email($email,$this->u_id);
		if($chk){
			$this->json(P_Lang('邮箱已被使用，请更换其他邮箱'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if($email == $user['email']){
			$this->json(P_Lang('邮箱与当前使用是一致的，不需要修改'));
		}
		$code = rand(1000,9999);
		//取得邮件模板
		$info = $this->model('email')->tpl('email_code');
		if(!$info){
			$this->json(P_Lang('未设置邮件验证码模板email_code，请联系管理员'));
		}
		$this->assign('email',$email);
		$this->assign('code',$code);
		$this->assign('user',$user);
		$fullname = $user['fullname'] ? $user['fullname'] : $user['user'];
		$title = $this->fetch($info['title'],'content');
		$content = $this->fetch($info['content'],'content');
		$data = array('title'=>$title,'content'=>$content,'email'=>$email,'fullname'=>$fullname);
		$action = $this->model('gateway')->action($server,$data);
		if($action){
			$this->model('gateway')->save_temp($code,$server['id'],$this->u_id);
			$this->json(true);
		}
		$this->json(P_Lang('邮件发送失败，请检查'));
	}
	
	//更新发票信息
	public function invoice_f()
	{
		$invoice_type = $this->get("invoice_type");
		if(!$invoice_type)
		{
			$this->json(P_Lang('发票类型不能为空'));
		}
		$this->model('user')->update_invoice_type($invoice_type,$this->u_id);
		$invoice_title = $this->get("invoice_title");
		if(!$invoice_title)
		{
			$this->json(P_Lang('发票抬头不能为空'));
		}
		$this->model('user')->update_invoice_title($invoice_title,$this->u_id);
		if(!$this->is_client){
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	//更新地址信息
	public function address_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('address')->get_one($id);
			if(!$rs){
				$this->json(P_Lang('地址信息不存在'));
			}
			if($rs['user_id'] != $this->u_id){
				$this->json(P_Lang('地址信息与账号不匹配'));
			}
		}
		$data = array();
		$data['type_id'] = $this->get('type') == 'billing' ? 'billing' : 'shipping';
		$data['fullname'] = $this->get('fullname');
		if(!$data['fullname']){
			$this->json(P_Lang('姓名不能为空'));
		}
		$data['gender'] = $this->get('gender','int');
		$data['country'] = $this->get('country');
		if(!$data['country']){
			$data['country'] = '中国';
		}
		$data['province'] = $this->get('province');
		if(!$data['province']){
			$this->json(P_Lang('请选择收件人省份信息'));
		}
		$data['city'] = $this->get('city');
		$data['county'] = $this->get('county');
		$data['address'] = $this->get('address');
		if(!$data['address']){
			$this->json(P_Lang('请填写地址信息，要求尽可能详细'));
		}
		$data['zipcode'] = $this->get('zipcode');
		if(!$data['zipcode']){
			$this->json(P_Lang('邮编不能为空'));
		}
		$data['tel'] = $this->get('tel');
		$data['mobile'] = $this->get('mobile');
		if(!$data['tel'] && !$data['mobile']){
			$this->json(P_Lang('请填写联系电话或手机号'));
		}
		if($data['tel']){
			$type = substr($data['tel'],0,3) == '400' ? '400' : 'tel';
			if(!$this->lib('common')->tel_check($data['tel'],$type)){
				$this->json(P_Lang('电话填写不正确，请规范填写，如：0755-123456789 或 400123456'));
			}
		}
		if($data['mobile']){
			$type = substr($data['mobile'],0,3) == '400' ? '400' : 'mobile';
			if(!$this->lib('common')->tel_check($data['mobile'],$type)){
				$this->json(P_Lang('手机填写不正确，请规范填写，要求以13，15，17，18开头的11位数字 或 400 电话'));
			}
		}
		if($id){
			if($rs['type_id'] != $data['type_id']){
				$this->json(P_Lang('地址类型不匹配'));
			}
			$this->model('address')->save($data,$id);
			$this->json(P_Lang('地址信息更新成功'),true);
		}
		$insert_id = $this->model('address')->save($data);
		if(!$insert_id){
			$this->json(P_Lang('地址信息创建存储失败'));
		}
		$this->json(P_Lang('地址信息创建成功'),true);
	}

	public function address_default_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您没有权限操作此地址信息'));
		}
		$this->model('user')->address_default($id);
		$this->json(true);
	}

	public function address_setting_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			$chk = $this->model('user')->address_one($id);
			if(!$chk || $chk['user_id'] != $this->u_id){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}else{
			$array['user_id'] = $this->u_id;
		}
		$country = $this->get('country');
		if(!$country){
			$country = '中国';
		}
		$array['country'] = $country;
		$array['province'] = $this->get('pca_p');
		$array['city'] = $this->get('pca_c');
		$array['county'] = $this->get('pca_a');
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->json(P_Lang('收件人姓名不能为空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->json(P_Lang('手机或固定电话必须有填写一项'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				$this->json(P_Lang('手机号格式不对，请填写11位数字'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				$this->json(P_Lang('电话格式不对'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				$this->json(P_Lang('邮箱格式不对'));
			}
		}
		$this->model('user')->address_save($array,$id);
		$this->json(true);
	}

	public function address_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您没有权限操作此地址信息'));
		}
		$this->model('user')->address_delete($id);
		$this->json(true);
	}

	public function invoice_setting_f()
	{
		$id = $this->get('id','int');
		$type = $this->get('type');
		$title = $this->get('title');
		if(!$title){
			$title = P_Lang('个人发票');
		}
		$content = $this->get('content');
		if(!$content){
			$content = P_Lang('明细');
		}
		$note = $this->get('note');
		$array = array('user_id'=>$this->u_id,'type'=>$type,'title'=>$title,'content'=>$content,'note'=>$note);
		$this->model('user')->invoice_save($array,$id);
		$this->json(true);
	}

	public function invoice_default_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->invoice_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您没有权限操作此信息'));
		}
		$this->model('user')->invoice_default($id);
		$this->json(true);
	}

	public function invoice_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->invoice_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您没有权限操作此地址信息'));
		}
		$this->model('user')->invoice_delete($id);
		$this->json(true);
	}
}
?>