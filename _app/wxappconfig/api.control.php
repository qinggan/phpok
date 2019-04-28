<?php
/**
 * 微信小程序应用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月31日
**/
namespace phpok\app\control\wxappconfig;

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
	
	public function login_f()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未绑定Code信息，无法登录'));
		}
		$rs = $this->model('wxappconfig')->get_one();
		if(!$rs){
			$this->error(P_Lang('小程序服务端参数没有配置好，请联系管理员'));
		}
		if(!$rs['wxapp_id'] || !$rs['wxapp_secret']){
			$this->error(P_Lang('小程序ID或密钥未配置好，请联系管理员配置'));
		}
		$url ='https://api.weixin.qq.com/sns/jscode2session?appid='.$rs['wxapp_id'];
		$url.= '&secret='.$rs['wxapp_secret'];
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
			$this->error(P_Lang('获取会员的OpenID为空'));
		}
		$openid = $info['openid'];
		$unionid = '';
		if($info['unionid']){
			$unionid = $info['unionid'];
		}
		$wx_user = $this->model('wxappconfig')->get_user($openid,$unionid);
		if(!$wx_user){
			$user_id = $this->_account_create($openid,$unionid);
		}else{
			$user_id = $this->_account_update($wx_user,$openid,$unionid);
		}
		if(!$user_id){
			$this->error(P_Lang('会员登录失败，请联系管理员'));
		}
		$user = $this->model('user')->get_one($user_id);
		$this->session->assign('user_id',$user['id']);
		$this->session->assign('user_name',$user['user']);
		$this->session->assign('user_gid',$user['group_id']);
		$array = array();
		$array['session_name'] = $this->session->sid();
		$array['session_val'] = $this->session->sessid();
		$array['login_status'] = true;
		$this->success($array);
	}

	public function openid_f()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未绑定Code信息，无法登录'));
		}
		$rs = $this->model('wxappconfig')->get_one();
		if(!$rs){
			$this->error(P_Lang('小程序服务端参数没有配置好，请联系管理员'));
		}
		if(!$rs['wxapp_id'] || !$rs['wxapp_secret']){
			$this->error(P_Lang('小程序ID或密钥未配置好，请联系管理员配置'));
		}
		$url ='https://api.weixin.qq.com/sns/jscode2session?appid='.$rs['wxapp_id'];
		$url.= '&secret='.$rs['wxapp_secret'];
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
			$this->error(P_Lang('获取会员的OpenID为空'));
		}
		$this->session->assign('wx_open_id',$info['openid']);
		$this->success($info['openid']);
	}
	
	private function _account_create($openid,$unionid='')
	{
		$data = array('openid'=>$openid);
		$data['unionid'] = $unionid;
		$data['lastlogin'] = $this->time;
		$data['headimg'] = $this->get('headimg');
		$data['nickname'] = $this->get('nickname');
		$data['sex'] = $this->get('gender');
		$data['country'] = $this->get('country');
		$data['province'] = $this->get('province');
		$data['city'] = $this->get('city');
		//检测昵称在账号中是否启用
		
		$tmp = $this->model('user')->get_one($data['nickname'],'user',false,false);
		$account = $tmp ? ($data['nickname'].'-'.$this->time) : $data['nickname'];
		$group = $this->model('usergroup')->get_default(true);
		$user = array('user'=>$account);
		if($group){
			$user['group_id'] = $group['id'];
		}
		$user['status'] = 1;
		$user['regtime'] = $this->time;
		$user['avatar'] = $data['headimg'];
		$insert_id = $this->model('user')->save($user);
		if(!$insert_id){
			return false;
		}
		$ext = array('id'=>$insert_id);
		$this->model('user')->save_ext($ext);
		$data['uid'] = $insert_id;
		$this->model('wxappconfig')->save_user($data);
		return $insert_id;
	}
	
	private function _account_update($wxuser,$openid='',$unionid='')
	{
		if(!$wxuser || !is_array($wxuser)){
			return false;
		}
		$data = array();
		$data['openid'] = $openid ? $openid : $wxuser['openid'];
		$data['unionid'] = $unionid ? $unionid : $wxuser['unionid'];
		$data['headimg'] = $this->get('headimg');
		$data['nickname'] = $this->get('nickname');
		$data['sex'] = $this->get('gender');
		$data['country'] = $this->get('country');
		$data['province'] = $this->get('province');
		$data['city'] = $this->get('city');
		if(!$wxuser['uid']){
			//登记会员信息
			$tmp = $this->model('user')->get_one($data['nickname'],'user',false,false);
			$account = $tmp ? ($data['nickname'].'-'.$this->time) : $data['nickname'];
			$group = $this->model('usergroup')->get_default(true);
			$user = array('user'=>$account);
			if($group){
				$user['group_id'] = $group['id'];
			}
			$user['status'] = 1;
			$user['regtime'] = $this->time;
			$user['avatar'] = $data['headimg'];
			$insert_id = $this->model('user')->save($user);
			if(!$insert_id){
				return false;
			}
			$ext = array('id'=>$insert_id);
			$this->model('user')->save_ext($ext);
			$data['uid'] = $insert_id;
		}else{
			$user = array('avatar'=>$data['headimg']);
			$this->model('user')->save($user,$wxuser['uid']);
			$data['uid'] = $wxuser['uid'];
		}
		$this->model('wxappconfig')->save_user($data);
		return $data['uid'];
	}
}
