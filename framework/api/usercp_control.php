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
	private $u_id; //会员ID
	public function __construct()
	{
		parent::control();
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还没有登录，请先登录或注册'));
		}
		$this->u_id = $this->session->val('user_id');
	}
	
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能获取个人信息'));
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
		$group_rs = $this->model('usergroup')->group_rs($this->u_id);
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
				$this->model('user')->update_ext($ext,$this->u_id);
			}
		}
		$this->success();
	}

	/**
	 * 更新会员头像
	**/
	public function avatar_f()
	{
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
			$save_pic = 'res/user/'.$this->u_id.'.'.$ext;
			$this->lib('file')->rm($this->dir_root.$save_pic);
			$this->lib('file')->save_pic($content,$this->dir_root.$save_pic);
			//生成正方式
			$this->lib('gd')->thumb($this->dir_root.$save_pic,$this->u_id,100,100);
			$this->lib('file')->mv('res/user/_'.$this->u_id.'.'.$ext,$save_pic);
			$this->model('user')->update_avatar($save_pic,$this->u_id);
			$this->success();
		}
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

	/**
	 * 更新会员密码功能
	**/
	public function passwd_f()
	{
		$user = $this->model('user')->get_one($this->u_id);
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
		$this->model('user')->update_password($password,$this->u_id);
		$this->model('user')->update_session($this->u_id);
		$this->success();
	}

	/**
	 * 更新会员手机
	**/
	public function mobile_f()
	{
		$pass = $this->get('pass');
		if(!$pass){
			$this->error(P_Lang('密码不能为空'));
		}
		$newmobile = $this->get("mobile");
		if(!$newmobile){
			$this->error(P_Lang('新手机号码不能为空'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if(!password_check($pass,$user['pass'])){
			$this->error(P_Lang('密码填写错误'));
		}
		if($user['mobile'] == $newmobile){
			$this->error(P_Lang('新旧手机号码不能一样'));
		}
		$uid = $this->model('user')->uid_from_mobile($newmobile,$this->u_id);
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
		$this->model('user')->update_mobile($newmobile,$this->u_id);
		$this->success();
	}

	/**
	 * 更新会员邮箱
	**/
	public function email_f()
	{
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
		$user = $this->model('user')->get_one($this->u_id);
		if($user['email'] == $email){
			$this->error(P_Lang('新旧邮箱不能一样'));
		}
		$chk = $this->model('user')->uid_from_email($email,$this->u_id);
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
		$this->model('user')->save(array('email'=>$email),$this->u_id);
		$this->model('user')->update_session($this->u_id);
		$this->success();
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
		$this->model('user')->update_session($this->u_id);
		$this->json(true);
	}

	/**
	 * 获取会员的收货地址信息
	**/
	public function address_f()
	{
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if(!$rslist){
			$this->error(P_Lang('会员暂无收货地址信息'));
		}
		$total = count($rslist);
		$default = $first = array();
		foreach($rslist as $key=>$value){
			if($key<1){
				$first = $value;
			}
			if($value['is_default']){
				$default = $value;
			}
		}
		if(!$default){
			$default = $first;
		}
		$array = array('total'=>$total,'rs'=>$default,'rslist'=>$rslist);
		$this->success($array);
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

	/**
	 * PHPOK5版会员收货地址保存
	**/
	public function address_save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			$chk = $this->model('user')->address_one($id);
			if(!$chk || $chk['user_id'] != $this->u_id){
				$this->error(P_Lang('您没有权限执行此操作'));
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
			$this->error(P_Lang('手机或固定电话必须有填写一项'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				$this->error(P_Lang('手机号格式不对，请填写11位数字'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				$this->error(P_Lang('电话格式不对'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				$this->error(P_Lang('邮箱格式不对'));
			}
		}
		if($id){
			$this->model('user')->address_save($array,$id);
		}else{
			$id = $this->model('user')->address_save($array);
			if(!$id){
				$this->error(P_Lang('地址添加失败'));
			}
		}
		$is_default = $this->get('is_default','checkbox');
		if($is_default){
			$this->model('user')->address_default($id);
		}
		$this->success($id);
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
		$rslist = $this->model('user')->list_relation($this->session->val('user_id'),$offset,$psize);
		if(!$rslist){
			$this->error(P_Lang('没有找到推荐人信息'));
		}
		$this->success($rslist);
	}
}