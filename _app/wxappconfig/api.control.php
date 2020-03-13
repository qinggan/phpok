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

	public function config_f()
	{
		if(!file_exists($this->dir_data.'wxappconfig.php')){
			$this->error(P_Lang('未配置服务端小程序'));
		}
		include_once($this->dir_data.'wxappconfig.php');
		unset($wxconfig['wxapp_secret']);//安全考虑，去除密钥
		$this->success($wxconfig);
	}

	public function code_f()
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
		$this->success();
	}

	/**
	 * 会员登录（非会员自动注册）
	 * @参数 code 通过 wx.login 获取到的 code 信息，用于获取 session_key 和 openid
	 * @参数 enData 获取手机号密文【包括敏感数据在内的完整用户信息的加密数据】
	 * @参数 iv 获取手机号【加密算法的初始向量】
	 * @参数 share_uid 推荐人ID
	 * @参数 share_code 推荐码
	 * @参数 nickname 微信昵称，如果昵称未被使用，则为会员账号
	 * @参数 headimg 微信头像
	 * @参数 gender 性别
	 * @参数 country 国家
	 * @参数 province 省份
	 * @参数 city 城市
	 * @参数 language 语言标识
	**/
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
		//判断是否有手机号
		$enData = $this->get('enData');
		$iv = $this->get('iv');
		$mobile = '';
		if($enData && $iv && $info['session_key']){
			$this->lib('weixin')->app_id($rs['wxapp_id']);
			$this->lib('weixin')->session_key($info['session_key']);
			$tmp = $this->lib('weixin')->decode($enData,$iv);
			$mobile = $tmp['phoneNumber'];
		}
		//检测会员是否存在
		if($mobile){
			$user = $this->model('user')->get_one($mobile,'mobile',false,false);
			if($user){
				if(!$user['status']){
					$this->error(P_Lang('您账号未审核，请联系管理员'));
				}
				if($user['status'] == 2){
					$this->error(P_Lang('您的账号已锁定，请联系管理员'));
				}
				$this->_account($openid,$unionid,$user['id']);
				$this->session->assign('user_id',$user['id']);
				$this->session->assign('user_name',$user['user']);
				$this->session->assign('user_gid',$user['group_id']);
				$this->session->assign('wx_openid',$openid);
				$this->session->assign('is_miniprogram',true);
				$this->success();
			}
		}
		$wx_user = $this->model('wxappconfig')->get_user($openid);
		if($wx_user && $wx_user['uid']){
			$user = $this->model('user')->get_one($wx_user['uid'],'id',false,false);
			if($user){
				if(!$user['status']){
					$this->error(P_Lang('您账号未审核，请联系管理员'));
				}
				if($user['status'] == 2){
					$this->error(P_Lang('您的账号已锁定，请联系管理员'));
				}
				$this->_account($openid,$unionid,$user['id']);
				$this->session->assign('user_id',$user['id']);
				$this->session->assign('user_name',$user['user']);
				$this->session->assign('user_gid',$user['group_id']);
				$this->session->assign('wx_openid',$openid);
				$this->session->assign('is_miniprogram',true);
				$this->success();
			}
			$wx_user['uid'] = 0;
		}
		$nickname = $this->get('nickname');
		$tmp = $this->model('user')->get_one($nickname,'user',false,false);
		$account = $tmp ? uniqid("WX-") : $nickname;
		$group = $this->model('usergroup')->get_default(true);
		$data = array('user'=>$account,'group_id'=>$group['id'],'mobile'=>$mobile,'status'=>1);
		$data['regtime'] = $this->time;
		$data['avatar'] = $this->get('headimg');
		$insert_id = $this->model('user')->save($data);
		if(!$insert_id){
			$this->error(P_Lang('会员注册失败，请联系管理员'));
		}
		$ext = array('id'=>$insert_id);
		$this->model('user')->save_ext($ext);
		//获取推荐人
		$relaction_id = $this->get('share_uid','int');
		if(!$relaction_id){
			$code = $this->get('share_code');
			if($code){
				$chk = $this->model('user')->get_one($code,'code',false,false);
				if($chk){
					$relaction_id = $chk['id'];
				}
			}
		}
		if(!$relaction_id && $this->session->val('introducer')){
			$relaction_id = $this->session->val('introducer');
		}
		if($relaction_id){
			$this->model('user')->save_relation($insert_id,$relaction_id);
		}
		$user = $this->model('user')->get_one($insert_id);
		$this->session->assign('user_id',$user['id']);
		$this->session->assign('user_name',$user['user']);
		$this->session->assign('user_gid',$user['group_id']);
		$this->session->assign('wx_openid',$openid);
		$this->session->assign('is_miniprogram',true);
		$this->success();
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
		$this->session->assign('wx_openid',$info['openid']);
		$this->session->assign('is_miniprogram',true);
		$this->success($info['openid']);
	}
	
	private function _account($openid='',$unionid='',$userid=0)
	{
		$data = array();
		$data['openid'] = $openid;
		$data['unionid'] = $unionid;
		$data['headimg'] = $this->get('headimg');
		$data['nickname'] = $this->get('nickname');
		$data['sex'] = $this->get('gender');
		$data['country'] = $this->get('country');
		$data['province'] = $this->get('province');
		$data['city'] = $this->get('city');
		$data['uid'] = $userid;
		$wxuser = $this->model('wxappconfig')->get_user($openid);
		if($wxuser){
			return $this->model('wxappconfig')->update_save($data,$wxuser['id']);
		}
		return $this->model('wxappconfig')->insert_save($data);
	}

	/**
	 * 修改背景色
	 * @参数 color 背景颜色代码值，格式是 #FFFFFF
	**/
	public function bgcolor_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		//检测字段
		$flist = $this->model('user')->fields_all('','identifier');
		if(!$flist){
			$this->error(P_Lang('无扩展字段'));
		}
		$keys = array_keys($flist);
		if(!in_array('bgcolor',$keys)){
			$this->error(P_Lang('未配置存储背景颜色的字段：bgcolor，请联系管理员设置'));
		}
		$flist = $this->model('user')->tbl_fields_list('user_ext');
		if(!in_array('bgcolor',$flist)){
			$this->error(P_Lang('会员表里缺少 bgcolor 字段，请检查'));
		}
		$color = $this->get('color');
		if(!$color){
			$color = '#FFFFFF';
		}
		if(substr($color,0,1) != '#'){
			$color = '#'.$color;
		}
		$array = array('bgcolor'=>$color);
		$this->model('user')->update_ext($array,$this->session->val('user_id'));
		$this->success();
	}
	
	/**
	 * 修改背景图片
	 * @参数 bgimg 格式是字段串，背景图片地址
	**/
	public function bgimg_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		//检测字段
		$flist = $this->model('user')->fields_all('','identifier');
		if(!$flist){
			$this->error(P_Lang('无扩展字段'));
		}
		$keys = array_keys($flist);
		if(!in_array('bgimg',$keys)){
			$this->error(P_Lang('未配置存储背景颜色的字段：bgimg，请联系管理员设置'));
		}
		$flist = $this->model('user')->tbl_fields_list('user_ext');
		if(!in_array('bgimg',$flist)){
			$this->error(P_Lang('会员表里缺少 bgimg 字段，请检查'));
		}
		$bgimg = $this->get('bgimg');
		$array = array('bgimg'=>$bgimg);
		$this->model('user')->update_ext($array,$this->session->val('user_id'));
		$this->success();
	}
}
