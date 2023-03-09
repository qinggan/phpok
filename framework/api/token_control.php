<?php
/**
 * 用户 Token 及 refreshToken
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2022年1月7日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class token_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->refresh_f();
	}

	public function header_info($token='',$config=array())
	{
		if(!$token){
			$token = $_SERVER['HTTP_AUTHORIZATION'] ? $_SERVER['HTTP_AUTHORIZATION'] : $_SERVER['HTTP_TOKEN'];
		}
		if(!$token){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证信息不存在'));
		}
		$tmps = explode(" ",$token);
		if($tmps[1]){
			$token = $tmps[1];
		}
		list($type, $string) = explode(':', base64_decode($token));
		$this->lib('token')->etype('api_code');
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry(24*60*60);
		$decode_data = $this->lib('token')->decode($string);
		if(!$decode_data || !is_array($decode_data)){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败'));
		}
		if(!$decode_data['session_id']){
			$this->error(P_Lang('HTTP_AUTHORIZATION 认证失败，Session 丢失'));
		}
		$this->session()->sessid($decode_data['session_id']);
		return true;
	}

	public function create_f()
	{
		$string = $this->get('data','html');
		if(!$string){
			$this->error(P_Lang('未接收到客户端发送的请求数据'));
		}
		$config = $this->model('config')->get_all();
		if(!$config || !$config['private_key'] || !$config['public_key'] || !$config['api_code']){
			$this->error(P_Lang('系统配置不全，请系统管理员到后台配置好公钥，密钥及API验证串'));
		}
		$this->lib("token")->etype("public_key");
		$this->lib('token')->public_key($config['public_key']);
		$this->lib('token')->private_key($config['private_key']);
		$this->lib('token')->expiry(365*24*60*60);
		$info = $this->lib('token')->decode($string);
		if(!$info){
			$this->error(P_Lang('密钥为空'));
		}
		$keyid = $info['api_code'];
		$auth_token = $this->get('authToken','html');
		if($auth_token){
			$this->header_info($auth_token,$config);
		}
		$user_token = $this->get('userToken','html');
		$user = array();
		if($user_token){
			$user = $this->token2user($user_token,$config);
			if($user){
				$this->model('user')->login($user);
				$user_token = $this->user_token($user['id'],$config);
			}
		}
		$auth_token = $this->auth_token($config,$user['id']);
		//生成验签密码
		$data = array();
		$expire_time = 365*24*60*60;
		$expiry = $this->time + $expire_time;
		$data = array("auth_time"=>($this->time + 7200),"auth_token"=>$auth_token);
		$data['refresh_token'] = $this->refresh_token($config,$keyid,$user['id']);
		$data['refresh_time'] = $expiry;
		if($user_token){
			$data['user_token'] = $user_token;
		}
		$this->lib("token")->etype("api_code");
		$this->lib('token')->keyid($keyid);
		$this->lib('token')->expiry($expire_time);
		$out = $this->lib('token')->encode($data);
		$this->success($out);
	}

	/**
	 * 用户Token生成
	 * @参数 $user_id 用户ID
	 * @参数 $config 参数配置，不清楚请留空
	 * @返回：加密后的用户信息
	**/
	public function user_token($user_id=0,$config=array())
	{
		if(!$user_id){
			return false;
		}
		if(!$config){
			$config = $this->model('config')->get_all();
			if(!$config || !$config['api_code']){
				return false;
			}
		}
		$user = $this->model('user')->get_one($user_id);
		if(!$user){
			return false;
		}
		//生成新的密钥，防止窜改
		$sign = md5($config['api_code'].'-'.$user['id'].'-'.$user['regtime']);
		$this->lib('token')->etype('api_code');
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry(365*24*60*60);
		$enData = array('id'=>$user['id'],'sign'=>$sign);
		$info = $this->lib('token')->encode($enData);
		return $info;
	}

	/**
	 * 用户Token转用户信息
	**/
	public function token2user($token='',$config=array())
	{
		if(!$token){
			return false;
		}
		if(!$config){
			$config = $this->model('config')->get_all();
			if(!$config || !$config['api_code']){
				return false;
			}
		}
		$this->lib('token')->etype('api_code');
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry(365*24*60*60);
		$info = $this->lib('token')->decode($token);
		if(!$info || !is_array($info) || !$info['id'] || !$info['sign']){
			return false;
		}
		$user = $this->model('user')->get_one($info['id']);
		if(!$user){
			return false;
		}
		if(!$user['status'] || $user['status'] == 2){
			return false;
		}
		$sign = md5($config['api_code'].'-'.$user['id'].'-'.$user['regtime']);
		if($sign != $info['sign']){
			return false;
		}
		return $user;
	}

	/**
	 * 生成签名码，签名码有效时间默认二个小时
	**/
	private function auth_token($config,$user_id=0)
	{
		if(!$config){
			$config = $this->model('config')->get_all();
			if(!$config || !$config['api_code']){
				return false;
			}
		}
		$time = $this->time;
		$data = array();
		$data['session_id'] = $this->session->sessid();
		$data['time'] = $time;
		$data['ip'] = $this->lib('common')->ip();
		if($user_id){
			$data['user_id'] = $user_id;
		}
		ksort($data);
		$expire_time = 7200;
		$this->lib('token')->etype('api_code');
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry($expire_time);
		$info = $this->lib('token')->encode($data);
		$token = base64_encode('code:'.$info);
		return $token;
	}

	private function refresh_token($config,$keyid=0,$user_id=0)
	{
		if(!$config){
			$config = $this->model('config')->get_all();
			if(!$config || !$config['api_code']){
				return false;
			}
		}
		$time = $this->time;
		$data = array();
		$data['keyid'] = $keyid;
		$data['time'] = $time;
		if($user_id){
			$data['user_id'] = $user_id;
			$data['sign'] = md5($config['api_code'].'-'.$time.'-'.$user_id);
		}else{
			$data['sign'] = md5($config['api_code'].'-'.$time.'-'.$keyid);
		}
		$this->lib("token")->etype("api_code");
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry(365*24*60*60);
		$info = $this->lib('token')->encode($data);
		return $info;
	}

	/**
	 * 通过 refresh 获取新的 Token
	**/
	public function refresh_f()
	{
		$token = $this->get('data','html');
		if(!$token){
			$this->error(P_Lang('未指定 refreshToken 信息'));
		}
		$config = $this->model('config')->get_all();
		if(!$config || !$config['private_key'] || !$config['public_key'] || !$config['api_code']){
			$this->error(P_Lang('系统配置不全，请系统管理员到后台配置好公钥，密钥及API验证串'));
		}
		//对 token 数据进行解密
		$this->lib("token")->etype("api_code");
		$this->lib('token')->keyid($config['api_code']);
		$this->lib('token')->expiry(365*24*60*60);
		$decode = $this->lib('token')->decode($token);
		if(!$decode){
			$this->error(P_Lang('解密失败，请检查'));
		}
		$data = array();
		$decode_data = is_string($decode) ? $this->lib('json')->decode($decode) : $decode;
		$user_token = '';
		if($decode_data['user_id']){
			$sign = md5($config['api_code'].'-'.$decode_data['time'].'-'.$decode_data['user_id']);
			if($sign != $decode_data['sign']){
				$this->error('签名不正确，验证不通过');
			}
			$user = $this->model('user')->get_one($decode_data['user_id']);
			if($user && ($user['status'] == 1 || $user['status'] == 3)){
				$this->model('user')->login($user);
				$user_token = $this->user_token($decode_data['user_id'],$config);
			}
		}else{
			$sign = md5($config['api_code'].'-'.$decode_data['time'].'-'.$decode_data['keyid']);
			if($sign != $decode_data['sign']){
				$this->error('签名不正确，验证不通过');
			}
		}
		$data['auth_token'] = $this->auth_token($config,$user['id']);
		$data['auth_time'] = $this->time + 7200;
		$data['refresh_token'] = $this->refresh_token($config,$decode_data['keyid'],$decode_data['user_id']);
		$data['refresh_time'] = $this->time + 365*24*60*60;
		if($user_token){
			$data['user_token'] = $user_token;
		}
		$this->success($data);
	}

	public function demo_f()
	{
		$config = $this->model('config')->get_all();
		if(!$config || !$config['private_key'] || !$config['public_key']){
			$this->error(P_Lang('系统配置不全，请系统管理员到后台配置好公钥，密钥及API验证串'));
		}
		$data = array();
		$data['title'] = '测试加密解密';
		$this->lib("token")->etype("public_key");
		$this->lib('token')->public_key($config['public_key']);
		$this->lib('token')->private_key($config['private_key']);
		$this->lib('token')->expiry(24*60*60);
		$tmp = $this->lib('token')->encode($data);
		$this->lib("token")->etype("public_key");
		$list = $this->lib('token')->decode($tmp);
		$this->success(array('encode'=>$tmp,'decode'=>$list));
	}
}
