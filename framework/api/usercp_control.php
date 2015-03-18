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
	function __construct()
	{
		parent::control();
		$token = $this->get('token');
		$token = ''; //因为Token机制在phpok里实现还不完善，之前的安装程序对密钥生成有异常，导致失败，故这里暂时停止基于密钥的验证
		if($token)
		{
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id'] || !$info['user_name'])
			{
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $info['user_id'];
			$this->u_name = $info['user_name'];
			$this->is_client = true;
		}
		else
		{
			if(!$_SESSION['user_id'])
			{
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->u_id = $_SESSION['user_id'];
			$this->u_name = $_SESSION['user_name'];
		}
	}

	//存储个人数据
	function info_f()
	{
		//获取会员组信息
		$group_rs = $this->model('usergroup')->group_rs($this->u_id);
		if(!$group_rs)
		{
			$this->json(P_Lang('会员组不存在'));
		}
		$avatar = $this->get("avatar");
		$email = $this->get("email");
		if(!$email)
		{
			$this->json(P_Lang('未指定邮箱'));
		}
		if(!phpok_check_email($email))
		{
			$this->json(P_Lang('邮箱不合法'));
		}
		//检测Email是否已被使用
		$uid = $this->model('user')->uid_from_email($email,$this->u_id);
		if($uid){
			$this->json(P_Lang('邮箱已被使用'));
		}
		//更新手机号
		$mobile = $this->get('mobile');
		$array = array('avatar'=>$avatar,'email'=>$email,'mobile'=>$mobile);
		$this->model('user')->save($array,$this->u_id);
		//读取扩展属性，并更新存储
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
	function avatar_f()
	{
		$data = $this->get('data');
		if(!$data)
		{
			$this->json(P_Lang('头像图片地址不能为空'));
		}
		$pInfo = pathinfo($data);
		$fileType = strtolower($pInfo['extension']);
		if(!$fileType || !in_array($fileType,array('jpg','gif','png','jpeg')))
		{
			$this->json(P_Lang('头像图片仅支持jpg,gif,png,jpeg'));
		}
		if(!file_exists($this->dir_root.$data))
		{
			$this->json(P_Lang('头像文件不存在'));
		}
		//更新会员头像
		$this->model('user')->update_avatar($data,$this->u_id);
		//非接口接入则更新相应的session信息
		if(!$this->is_client)
		{
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	//更新会员密码功能
	function passwd_f()
	{
		$oldpass = $this->get("oldpass");
		if(!$oldpass)
		{
			$this->json(P_Lang('旧密码不能为空'));
		}
		$newpass = $this->get("newpass");
		$chkpass = $this->get("chkpass");
		if(!$newpass || !$chkpass)
		{
			$this->json(P_Lang('新密码不能为空'));
		}
		if($newpass != $chkpass)
		{
			$this->json(P_Lang('新旧密码不一致'));
		}
		$user = $this->model('user')->get_one($this->u_id,false);
		if(!password_check($oldpass,$user["pass"]))
		{
			$this->json(P_Lang('旧密码输入错误'));
		}
		if($oldpass == $newpass)
		{
			$this->json(P_Lang('新旧密码不能一样'));
		}
		$password = password_create($newpass);
		$this->model('user')->update_password($password,$this->u_id);
		if(!$this->is_client)
		{
			$this->model('user')->update_session($this->u_id);
		}
		$this->json(true);
	}

	//更新地址信息
	public function address_f()
	{
		
		$id = $this->get('id','int');
		if($id)
		{
			$rs = $this->model('address')->get_one($id);
			if(!$rs)
			{
				$this->json(P_Lang('地址信息不存在'));
			}
			if($rs['user_id'] != $this->u_id)
			{
				$this->json(P_Lang('地址信息与账号不匹配'));
			}
		}
		$data = array();
		//地址类型，仅支持两种：一种是账单地址，一种是收货地址
		$data['type_id'] = $this->get('type') == 'billing' ? 'billing' : 'shipping';
		$data['fullname'] = $this->get('fullname');
		if(!$data['fullname'])
		{
			$this->json(P_Lang('姓名不能为空'));
		}
		$data['gender'] = $this->get('gender','int');
		$data['country'] = $this->get('country');
		if(!$data['country'])
		{
			$data['country'] = '中国';
		}
		$data['province'] = $this->get('province');
		if(!$data['province'])
		{
			$this->json(P_Lang('请选择收件人省份信息'));
		}
		$data['city'] = $this->get('city');
		$data['county'] = $this->get('county');
		$data['address'] = $this->get('address');
		if(!$data['address'])
		{
			$this->json(P_Lang('请填写收件人地址信息，要求尽可能详细'));
		}
		$data['zipcode'] = $this->get('zipcode');
		if(!$data['zipcode'])
		{
			$this->json(P_Lang('邮编不能为空'));
		}
		$data['tel'] = $this->get('tel');
		$data['mobile'] = $this->get('mobile');
		if(!$data['tel'] && !$data['mobile'])
		{
			$this->json(P_Lang('请填写联系电话或手机号'));
		}
		if($data['tel'])
		{
			$type = substr($data['tel'],0,3) == '400' ? '400' : 'tel';
			if(!$this->lib('common')->tel_check($data['tel'],$type))
			{
				$this->json(P_Lang('电话填写不正确，请规范填写，如：0755-123456789 或 400123456'));
			}
		}
		if($data['mobile'])
		{
			$type = substr($data['mobile'],0,3) == '400' ? '400' : 'mobile';
			if(!$this->lib('common')->tel_check($data['mobile'],$type))
			{
				$this->json(P_Lang('手机填写不正确，请规范填写，要求以13，15，17，18开头的11位数字 或 400 电话'));
			}
		}
		if($id)
		{
			if($rs['type_id'] != $data['type_id'])
			{
				$this->json(P_Lang('地址类型不匹配'));
			}
			$this->model('address')->save($data,$id);
			$this->json(P_Lang('地址信息更新成功'),true);
		}
		$insert_id = $this->model('address')->save($data);
		if(!$insert_id)
		{
			$this->json(P_Lang('地址信息创建存储失败'));
		}
		$this->json(P_Lang('地址信息创建成功'),true);
	}
}
?>