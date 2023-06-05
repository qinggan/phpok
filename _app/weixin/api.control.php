<?php
/**
 * 接口应用_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
namespace phpok\app\control\weixin;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 用户登录后进后账号绑定
	**/
	public function ap_bind_f()
	{
		$rs = $this->model('weixin')->config_one('ap');
		if(!$rs){
			$this->error(P_Lang('小程序参数未配置好，请联系管理员'));
		}
		$iv = $this->get('iv');
		$code = $this->get('code');
		$encryptedData = $this->get('encryptedData');
		$openid_data = $this->get('openid_data','html');
		$mobile = $openid = $unionid = '';
		if($openid_data){
			$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
			if(!$api_code){
				$this->error(P_Lang("系统未启用接口功能"));
			}
			$this->lib('token')->etype('api_code');
			$this->lib('token')->expiry(24*60*60);
			$this->lib('token')->keyid($api_code);
			$info = $this->lib('token')->decode($openid_data);
			if(!$info || !$info['openid']){
				$this->error('参数异常，请检查');
			}
			$openid = $info['openid'];
			$unionid = $info['unionid'];
			if($info['session_key'] && $iv && $encryptedData){
				$this->lib('weixin')->app_id($rs['app_id']);
				$this->lib('weixin')->session_key($info['session_key']);
				$tmp = $this->lib('weixin')->decode($encryptedData,$iv);
				if($tmp && $tmp['phoneNumber']){
					$mobile = $tmp['phoneNumber'];
				}
			}
		}
		if(!$mobile && $code){
			//通过Code获取用户信息
			$this->lib('weixin')->app_id($rs['app_id']);
			$this->lib('weixin')->app_secret($rs['app_id']);
			$phone_info = $this->lib('weixin')->getPhoneNumber($code);
			if(!$phone_info){
				$this->error('获取失败');
			}
			$mobile = $phone_info['phoneNumber'];
		}
		if(!$openid && !$mobile){
			$this->error('登录失败，请联系管理员');
		}
		$user_id = 0;
		if($mobile){
			//检测手机号用户是否存在
			$user = $this->model('user')->get_one($mobile,'mobile',false,false);
			if($user){
				$user_id = $user['id'];
			}
		}

		//如果存在wx_id
		//如果用户存在
		if(!$user_id){
			$data = array('mobile'=>$mobile,'status'=>1);
			$group_rs = $this->model('usergroup')->get_default(true);
			$group_id = $group_rs ? $group_rs['id'] : 0;
			$data['group_id'] = $group_id;
			$user_id = $this->model('user')->save($data);
			if(!$user_id){
				$this->error(P_Lang('用户创建失败，请联系管理员'));
			}
			$username = '*'.$user_id;
			$data = array('user'=>$username);
			$this->model('user')->save($data,$user_id);
		}
		//保存微信信息
		$data = array('openid'=>$openid,'user_id'=>$user_id,'source'=>'微信小程序');
		if($unionid){
			$data['unionid'] = $unionid;
		}
		$this->model('weixin')->user_save($data);
		$data = $this->model('user')->login($user_id,true);
		$token = $this->control('token','api')->user_token($user_id);
		if($token){
			$data['token'] = $token;
		}
		$this->session()->assign('wx_openid',$openid);
		$this->success($data);
	}

	public function index_f()
	{
		$this->success();
	}

	public function login_f()
	{
		$platform = $this->get('platform');
		if(!$platform){
			$this->error(P_Lang('未指定要登录的平台'));
			$platform = "mp";
		}
		//公众号
		if($platform == 'mp'){
			$this->login_mp();
		}
		//开放平台
		if($platform == 'op'){
			$this->login_op();
		}
		//小程序
		if($platform == 'ap'){
			$this->login_ap();
		}
		$this->error(P_Lang('指定的平台不存在'));
	}

	public function miniapp_config_f()
	{
		$rs = $this->model('weixin')->mini_app_config();
		if($rs && isset($rs['wxapp_secret'])){
			unset($rs['wxapp_secret']);
		}
		if(!$rs){
			$this->error('获取参数信息失败');
		}
		$this->success($rs);
	}

	/**
	 * 取得小程序微信的OpenID，主要用于免登录购买及支付
	**/
	public function ap_openid_f()
	{
		if($this->session->val('wx_openid')){
			$this->success($this->session->val('wx_openid'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未绑定Code信息'));
		}
		$rs = $this->model('weixin')->config_one('ap');
		if(!$rs){
			$this->error(P_Lang('小程序参数未配置好，请联系管理员'));
		}
		$ip = $this->model('weixin')->ip('api.weixin.qq.com');
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
		$this->lib('curl')->user_agent($this->lib('server')->agent());
		$url ='https://api.weixin.qq.com/sns/jscode2session?appid='.$rs['app_id'];
		$url.= '&secret='.$rs['app_secret'];
		$url.= '&js_code='.$code;
		$url.= '&grant_type=authorization_code';
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error(P_Lang('远程获取用户信息失败，请检查'));
		}
		if($info['errcode']){
			$this->error($info['errcode'].': '.$info['errmsg']);
		}
		if(!$info['openid']){
			$this->error(P_Lang('获取用户的OpenID为空'));
		}
		$this->session->assign('wx_openid',$info['openid']);
		$this->success($info['openid']);
	}

	/**
	 * 小程序用户登录
	**/
	private function login_ap()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未绑定Code信息，无法登录'));
		}
		$rs = $this->model('weixin')->config_one('ap');
		if(!$rs){
			$this->error(P_Lang('小程序参数未配置好，请联系管理员'));
		}
		//获取云端的api_code，用于加密获取到的数据
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		if(!$api_code){
			$this->error(P_Lang("系统未启用接口功能"));
		}
		//获取远程的openid和unionid
		$ip = $this->model('weixin')->ip('api.weixin.qq.com');
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
		$this->lib('curl')->user_agent($this->lib('server')->agent());
		$url ='https://api.weixin.qq.com/sns/jscode2session?appid='.$rs['app_id'];
		$url.= '&secret='.$rs['app_secret'];
		$url.= '&js_code='.$code;
		$url.= '&grant_type=authorization_code';
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error(P_Lang('远程获取用户信息失败，请检查'));
		}
		if($info['errcode']){
			$this->error($info['errcode'].': '.$info['errmsg']);
		}
		if(!$info['openid']){
			$this->error(P_Lang('获取用户的OpenID为空'));
		}
		$data = array('openid'=>$info['openid']);
		if($info['session_key']){
			$data['session_key'] = $info['session_key'];
		}
		if($info['unionid']){
			$data['unionid'] = $info['unionid'];
		}
		$this->lib('token')->etype('api_code');
		$this->lib('token')->expiry(24*60*60);
		$this->lib('token')->keyid($api_code);
		$token = $this->lib('token')->encode($data);
		$this->success($token);
	}

	/**
	 * 公众号登录
	**/
	private function login_mp()
	{
		//
	}

	//开放平台扫码登录
	private function login_op()
	{
		//
	}
}
