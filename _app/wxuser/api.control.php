<?php
/**
 * 接口应用_登记微信平台里所有会员，包括开放平台，公众平台及小程序平台
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月26日 03时25分
**/
namespace phpok\app\control\wxuser;
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
	 * 绑定，手机号 + 验证码 + 微信 OpenId
	**/
	public function bind_f()
	{
		$openid = $this->get('openid');
		if(!$openid){
			$this->error(P_Lang('未指定openid'));
		}
		$wx = $this->model('wxuser')->get_one($openid);
		if(!$wx){
			$this->error(P_Lang('微信用户信息不存在'));
		}
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->error(P_Lang('手机号不能为空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->error(P_Lang('手机号不正确'));
		}
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('验证码不能为空'));
		}
		$this->model('vcode')->type('sms');
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$rs = $this->model('user')->get_one($mobile,'mobile',false,false);
		if(!$rs){
			//注册成为新会员
			$data = array('user'=>$mobile,'mobile'=>$mobile,'status'=>1);
			$group_id = $this->get('group_id');
			$group_rs = false;
			if($group_id){
				$group_rs = $this->model('usergroup')->get_one($group_id);
			}
			if(!$group_rs){
				$group_rs = $this->model('usergroup')->get_default(true);
			}
			$group_id = $group_rs ? $group_rs['id'] : 0;
			$data['group_id'] = $group_id;
			$data['avatar'] = $wx['headimg'];
			$insert_id = $this->model('user')->save($data);
			if(!$insert_id){
				$this->error('会员创建失败，请联系管理员');
			}
			$ext = array('id'=>$insert_id);
			$flist = $this->model('user')->fields_all();
			if($flist){
				foreach($flist as $key=>$value){
					if($wx && $wx[$value['identifier']]){
						$ext[$value['identifier']] = $wx[$value['identifier']];
					}
				}
			}
			$this->model('user')->save_ext($ext);
			$rs = $this->model('user')->get_one($insert_id);
		}
		$this->model('wxuser')->update_user_id($openid,$rs['id']);
		$this->session->assign('user_id',$rs['id']);
		$this->session->assign('user_gid',$rs['group_id']);
		$this->session->assign('user_name',$rs['user']);
		//登录送积分
		$this->model('wealth')->login($rs['id'],P_Lang('会员登录'));
		$this->plugin('plugin-login-save',$rs['id']);
		$this->success(array('user_id'=>$rs['id']));
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
		if($platform == 'mp'){
			$this->login_mp();
		}
		if($platform == 'op'){
			$this->login_op();
		}
		if($platform == 'ap'){
			$this->login_ap();
		}
		$this->error(P_Lang('指定的平台不存在'));
	}

	/**
	 * 小程序会员登录
	**/
	private function login_ap()
	{
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未绑定Code信息，无法登录'));
		}
		$rs = $this->model('wxconfig')->get_one('ap');
		if(!$rs){
			$this->error(P_Lang('小程序服务端参数没有配置好，请联系管理员'));
		}
		$ip = $this->model('wxconfig')->ip('api.weixin.qq.com');
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
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
			$this->error(P_Lang('获取会员的OpenID为空'));
		}
		$data = array('openid'=>$info['openid']);
		$data['nickname'] = $this->get('nickname');
		$data['headimg'] = $this->get('headimg');
		$data['gender'] = $this->get('gender');
		$data['country'] = $this->get('country');
		$data['province'] = $this->get('province');
		$data['city'] = $this->get('city');
		$data['language'] = $this->get('language');
		$data['unionid'] = $info['unionid'];
		$data['source'] = '微信小程序';
		if($data['unionid']){
			$user_id = $this->model('wxuser')->unionid2uid($data['unionid']);
			if($user_id){
				$data['user_id'] = $user_id;
			}
		}
		$wx = $this->model('wxuser')->get_one($info['openid']);
		if(!$wx){
			$this->model('wxuser')->save($data);
			$wx = $data;
			unset($data);
		}
		if($wx['user_id']){
			//直接登录
			$user = $this->model('user')->get_one($wx['user_id'],'id',false,false);
			if($user){
				if(!$user['status']){
					$this->error(P_Lang('会员未审核，请联系管理员'));
				}
				if($user['status'] == 2){
					$this->error(P_Lang('会员已锁定，请联系管理员'));
				}
				$this->session->assign('user_id',$user['id']);
				$this->session->assign('user_name',$user['user']);
				$this->session->assign('user_gid',$user['group_id']);
				$this->model('wxuser')->lastlogin($info['openid']);
				//登录送积分
				$this->model('wealth')->login($user['id'],P_Lang('会员登录'));
				//登录节点
				$this->plugin('plugin-login-save',$user['id']);
				$this->success(array('user_id'=>$user['id']));
			}
			//更新会员ID
			$this->model('wxuser')->delete_user_id($openid);
		}
		//检测是否有手机号
		$enData = $this->get('enData');
		$iv = $this->get('iv');
		$mobile = '';
		if($enData && $iv && $info['session_key']){
			$this->lib('weixin')->app_id($rs['app_id']);
			$this->lib('weixin')->session_key($info['session_key']);
			$tmp = $this->lib('weixin')->decode($enData,$iv);
			if($tmp && $tmp['phoneNumber']){
				$mobile = $tmp['phoneNumber'];
			}
		}
		if($mobile){
			$user = $this->model('user')->get_one($mobile,'mobile',false,false);
			if(!$user){
				//注册成为会员
				$data = array('user'=>$mobile,'mobile'=>$mobile);
				$group = $this->model('usergroup')->get_default(true);
				if($group){
					$data['group_id'] = $group['id'];
				}
				$data['status'] = 1;
				$data['avatar'] = $wx['headimg'];
				$data['regtime'] = $this->time;
				$uid = $this->model('user')->save($data);
				if(!$uid){
					$this->error(P_Lang('会员注册失败，请检查'));
				}
				$ext = array('id'=>$uid);
				$fields_all = $this->model('user')->fields_all('','identifier');
				if($fields_all){
					foreach($fields_all as $key=>$value){
						if($wx[$key]){
							$ext[$key] = $wx[$key];
						}
					}
				}
				$this->model('user')->save_ext($ext);
				$this->model('wxuser')->update_user_id($wx['openid'],$uid);
				$share_uid = $this->get('share_uid');
				$share_code = $this->get('share_code');
				$relation_id = 0;
				if($share_uid){
					$relation = $this->model('user')->get_one($share_uid,'id',false,false);
					if($relation && $relation['status'] && $relation['status'] != 2){
						$relation_id = $relation['id'];
					}
				}
				if(!$relation_id && $share_code){
					$relation = $this->model('user')->get_one($share_uid,'code',false,false);
					if($relation && $relation['status'] && $relation['status'] != 2){
						$relation_id = $relation['id'];
					}
				}
				if(!$relation_id && $this->session->val('introducer')){
					$relation_id = $this->session->val('introducer');
				}
				if($relation_id){
					$this->model('user')->save_relation($uid,$relation_id);
				}
				//注册送积分
				$this->model('wealth')->register($uid,P_Lang('会员注册'));
				$this->session->assign('user_id',$uid);
				$this->session->assign('user_gid',$group['id']);
				$this->session->assign('user_name',$data["user"]);
				$this->success(array('user_id'=>$uid));
			}
			if($user && !$user['status']){
				$this->error(P_Lang('您绑定的手机号还未审核，请联系管理员'));
			}
			if($user && $user['status'] == 2){
				$this->error(P_Lang('您绑定的手机号已锁定，请联系管理员'));
			}
			$this->model('wxuser')->update_user_id($wx['openid'],$user['id']);
			$this->model('wealth')->login($uid,P_Lang('会员登录'));
			$this->session->assign('user_id',$user['id']);
			$this->session->assign('user_gid',$user['group_id']);
			$this->session->assign('user_name',$user["user"]);
			$this->success(array('user_id'=>$user['id']));
		}
		$this->session->assign('wx_openid',$wx['openid']);
		$data = array('openid'=>$wx['openid'],'mobile'=>$mobile);
		$data['avatar'] = $wx['avatar'];
		$this->tip($data);
	}

	/**
	 * 公众号登录
	**/
	private function login_mp()
	{
		//
	}

	private function login_op()
	{
		//
	}
}
