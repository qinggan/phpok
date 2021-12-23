<?php
/**
 * 网站前台_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
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
class www_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->display('www_index');
	}

	/**
	 * 微信开放平台登录
	**/
	public function op_login_f()
	{
		if($this->session->val('user_id')){
			$backurl = $this->session->val('_back') ? $this->session->val('_back') : $this->config['url'];
			$this->error('您已登录，不用重复登录',$backurl);
		}
		$ip = $this->model('weixin')->ip('api.weixin.qq.com');
		$config = $this->model('weixin')->config_one('op');
		if(!$config || !$config['app_id'] || !$config['app_secret']){
			$this->error('参数未配置好');
		}
		$state = $this->get('state');
		if(!$state || ($state && $state != $this->session->val('weixin_state'))){
			$this->error('登录有异常，请检查');
		}
		$code = $this->get('code');
		if(!$code){
			$this->error('授权登录异常，请检查');
		}
		$token_url  = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$config['app_id'];
		$token_url .= "&secret=".$config['app_secret'];
		$token_url .= "&code=".$code."&grant_type=authorization_code";
		$this->lib('curl')->user_agent($this->lib('server')->agnet());
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
		$response = $this->lib('curl')->get_content($token_url);
		if(strpos($response,"callback") !== false){
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
		}
		$params = $this->lib('json')->decode($response);
		if(isset($params['errcode'])){
			$this->error('登录异常，错误ID：'.$params['errcode'].'，错误描述：'.$params['errmsg']);
		}
		$token = $params['access_token'];
		$expires_in = $params['expires_in'] - 1800;
		$refresh_token = $params['refresh_token'];
		$openid = $params['openid'];
		//检测账号是否存在
		$wxinfo = $this->model('weixin')->user_one($openid,'微信开放平台');
		if($wxinfo){
			if($wxinfo['user_id']){
				//更新登录时间
				$data = array('lastlogin'=>$this->time,'source'=>'微信开放平台','openid'=>$openid);
				$this->model('weixin')->save($data);
				$this->_to_back($wxinfo['user_id']);
			}
			$user = $this->_save_account($wxinfo,$this->session->val('voucher'),'微信开放平台');
			if(!$user){
				$this->error('登录失败，请联系管理员');
			}
			$this->_to_back($user['id']);
		}
		//不存在
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".urlencode($token).'&openid='.$openid;
		$info = $this->lib('curl')->get_content($url);
		if(strpos($info,"callback") !== false){
			$lpos = strpos($info, "(");
			$rpos = strrpos($info, ")");
			$info = substr($info, $lpos + 1, $rpos - $lpos -1);
		}
		$wxinfo = $this->lib('json')->decode($info);
		if(isset($wxinfo['errcode'])){
			$this->error('登录异常，错误ID：'.$wxinfo['errcode'].'，错误描述：'.$wxinfo['errmsg']);
		}
		//存在账号
		$wxuser = $this->_save_weixin($wxinfo,'微信开放平台');
		if(!$wxuser){
			$this->error('登录失败，请联系管理员');
		}
		$this->_to_back($wxuser['id']);
	}

	/**
	 * 接收信息
	 * @参数 echostr 存在用于较检接口是否连通
	**/
	public function post_f()
	{
		$echostr = $this->get('echostr');
		if($echostr){
			return $this->_valid();
		}
		$data = file_get_contents("php://input");
		if($data){
			$info = $this->lib('xml')->read($data,false);
			if($info['MsgType'] == 'event'){
				$this->_event($info);
			}
			echo 'success';
			exit;
		}
		echo 'success';
		exit;
	}

	/**
	 * 定义事件
	**/
	private function _event($rs)
	{
		if(!$rs || !is_array($rs)){
			echo '';
			exit;
		}
		if($rs['Event'] == 'subscribe'){ //订阅通知
			return $this->_subscribe($rs);
		}
		if($rs['Event'] == 'unsubscribe'){   //取消订阅通知
			return $this->_unsubscribe($rs);
		}
		if($rs['Event'] == 'CLICK'){
			if($rs['EventKey'] == '_getcard'){
				$this->_click_getcard($rs);
			}
			$this->lib('weixin')->openid($rs['FromUserName']);
			$this->lib('weixin')->account($rs['ToUserName']);
			$sql = "SELECT module FROM ".$this->db->prefix."project WHERE id='".$this->me['param']['wx_projectid']."' AND status=1";
			$project_rs = $this->db->get_one($sql);
			if(!$project_rs || !$project_rs['module']){
				$this->lib('weixin')->error_xml('项目未配置成功或未绑定模块');
			}
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$project_rs['module']." WHERE project_id='".$this->me['param']['wx_projectid']."' AND wxcontent='".$rs['EventKey']."' LIMIT 1";
			$info = $this->db->get_one($sql);
			if(!$info || !$info['wxinfo']){
				$this->lib('weixin')->error_xml('未配置相应的动作触发');
			}
			$content = $this->fetch($info['wxinfo'],'content',false);
			$this->lib('weixin')->echo_xml($content);
		}
		if($rs['Event'] == 'VIEW'){
			//数据不会上报
			$sql = "SELECT uid FROM ".$this->db->prefix."weixin_user WHERE openid='".$rs['FromUserName']."'";
			$tmp = $this->db->get_one($sql);
			if($tmp){
				$this->session->assign('user_id',$tmp['uid']);
				$rs = $this->model('user')->get_one($tmp['uid']);
				$this->session->assign('user_gid',$rs['group_id']);
				$this->session->assign('user_name',$rs['user']);
			}
			echo "";
			exit;
		}
	}

	private function _create_account($wxinfo)
	{
		$wxconfig = $this->model('weixin')->config_subscribe();
		$account = 'wx-[uid]-[rand]';
		if($wxconfig['account']){
			$account = $wxconfig['account'];
		}
		//检测维一性
		$list = array('[uid]','[rand]','[time]','[nickname]');
		foreach($list as $key=>$value){
			if($value == '[uid]' && strpos($account,'[uid]') !== false){
				$sql = "SELECT max(id) FROM ".$this->db->prefix."user";
				$uid = $this->db->count($sql);
				if(!$uid){
					$uid = 0;
				}
				$uid++;
				$account = str_replace('[uid]',$uid,$account);
			}
			if($value == '[rand]' && strpos($account,'[rand]') !== false){
				$rand = rand(1000,9999);
				$account = str_replace('[rand]',$rand,$account);
			}
			if($value == '[time]' && strpos($account,'[time]') !== false){
				$account = str_replace('[time]',$this->time,$account);
			}
			if($value == '[nickname]' && strpos($account,'[nickname]') !== false){
				$account = str_replace('[nickname]',$wxinfo['nickname']);
			}
		}
		//检测用户账号是否存在，如果存在，仅使用系统自动生成规则
		$tmp = $this->model('user')->get_one($account,'user',false,false);
		if($tmp){
			$sql = "SELECT max(id) FROM ".$this->db->prefix."user";
			$uid = $this->db->count($sql);
			if(!$uid){
				$uid = 0;
			}
			$uid++;
			$account = 'wx'.$uid.'-'.rand(1000,9999);
		}
		return $account;
	}

	private function _create_password()
	{
		$wxconfig = $this->model('weixin')->config_subscribe();
		$password = 'WX[rand]';
		if($wxconfig['password']){
			$password = $wxconfig['password'];
		}
		$list = array('[rand]','[time]');
		foreach($list as $key=>$value){
			if($value == '[rand]' && strpos($password,'[rand]') !== false){
				$rand = rand(1000,9999);
				$password = str_replace('[rand]',$rand,$password);
			}
			if($value == '[time]' && strpos($password,'[time]') !== false){
				$password = str_replace('[time]',$this->time,$password);
			}
		}
		return $password;
	}

	private function _joinuser($main_uid=0,$ext_uid=0)
	{
		if(!$main_uid || !$ext_uid || $main_uid == $ext_uid){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."weixin_user SET user_id='".$main_uid."' WHERE user_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并主题数
		$sql = "UPDATE ".$this->db->prefix."list SET user_id='".$main_uid."' WHERE user_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并订单
		$sql = "UPDATE ".$this->db->prefix."order SET user_id='".$main_uid."' WHERE user_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并支付记录
		$sql = "UPDATE ".$this->db->prefix."payment_log SET user_id='".$main_uid."' WHERE user_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并回复
		$sql = "UPDATE ".$this->db->prefix."reply SET uid='".$main_uid."' WHERE uid='".$ext_uid."'";
		$this->db->query($sql);
		//合并附件
		$sql = "UPDATE ".$this->db->prefix."res SET user_id='".$main_uid."' WHERE user_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并介绍人
		$sql = "UPDATE ".$this->db->prefix."user_relation SET introducer='".$main_uid."' WHERE introducer='".$ext_uid."'";
		$this->db->query($sql);
		//合并财富日志
		$sql = "UPDATE ".$this->db->prefix."wealth_log SET goal_id='".$main_uid."' WHERE goal_id='".$ext_uid."'";
		$this->db->query($sql);
		//合并财富
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_info WHERE uid='".$ext_uid."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "SELECT * FROM ".$this->db->prefix."wealth_info WHERE uid='".$main_uid."' AND wid='".$value['wid']."'";
				$tmprs = $this->db->get_one($sql);
				if(!$tmprs){
					$array = $value;
					$array['uid'] = $main_uid;
					$this->db->insert_array($array,'wealth_info','replace');
				}else{
					$tmp = $tmprs['val'] + $value['val'];
					if($tmp<0){
						$tmp = 0;
					}
					$sql = "UPDATE ".$this->db->prefix."wealth_info SET val='".$tmp."',lasttime='".$this->time."' WHERE wid='".$value['wid']."' AND uid='".$main_uid."'";
					$this->db->query($sql);
				}
			}
			$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE uid='".$ext_uid."'";
			$this->db->query($sql);
		}
		//删除用户数据
		$this->model('user')->del($ext_uid);
		return true;
	}

	private function _save_account($wxinfo,$voucher='',$source='')
	{
		$chk = $this->_save_weixin($wxinfo,$source);
		if($chk && $chk['user_id']){
			$uid = $chk['user_id'];
			$u = $this->model('user')->get_one($chk['user_id'],'id',false,false);
			$account = $u['user'];
			$password = $this->_create_password();
			//更新用户
			$this->model('weixin')->user_save($wxuser);
			$data = array('pass'=>password_create($password));
			$data['avatar'] = $wxinfo['headimgurl'];
			$this->model('user')->save($data,$chk['user_id']);
			$ext = array();
			$flist = $this->model('user')->fields_all();
			if($flist){
				foreach($flist as $key=>$value){
					if($wxinfo && $wxinfo[$value['identifier']]){
						$ext[$value['identifier']] = $wxinfo[$value['identifier']];
					}
				}
			}
			if($ext && count($ext)>0){
				$this->model('user')->update_ext($ext,$chk['user_id']);
			}
		}else{
			$uid = 0;
			//检测是否有unionid
			$account = $this->_create_account($wxinfo);
			if($wxuser['unionid']){
				$uid = $this->model('weixin')->unionid2uid($wxuser['unionid']);
				if($uid){
					$u = $this->model('user')->get_one($uid,'id',false,false);
					if($u){
						$account = $u['user'];
					}else{
						$uid = 0;
					}
				}
			}
			$password = $this->_create_password();
			$ugroup = $this->model('usergroup')->get_default(1);
			$groupid = $ugroup ? $ugroup['id'] : 0;
			$data = array('group_id'=>$groupid,'user'=>$account,'pass'=>password_create($password),'status'=>1,'regtime'=>time());
			$data['avatar'] = $wxinfo['headimgurl'];
			if(!$uid){
				$uid = $this->model('user')->save($data);
				//保存用户扩展字段
				$ext = array('id'=>$uid);
				$flist = $this->model('user')->fields_all();
				if($flist){
					foreach($flist as $key=>$value){
						if(!$wxinfo || !$wxinfo[$value['identifier']]){
							continue;
						}
						$ext[$value['identifier']] = $wxinfo[$value['identifier']];
					}
				}
				$this->model('user')->save_ext($ext);
			}else{
				$data = array('pass'=>password_create($password));
				$data['avatar'] = $wxinfo['headimgurl'];
				$uid = $this->model('user')->save($data,$uid);
				$ext = array();
				$flist = $this->model('user')->fields_all();
				if($flist){
					foreach($flist as $key=>$value){
						if($wxinfo && $wxinfo[$value['identifier']]){
							$ext[$value['identifier']] = $wxinfo[$value['identifier']];
						}
					}
				}
				if($ext && count($ext)>0){
					$this->model('user')->update_ext($ext,$chk['user_id']);
				}
			}
			$wxuser['user_id'] = $uid;
			$this->model('weixin')->user_save($wxuser);//保存微信用户信息
		}
		//更新UNIONID与用户关系
		if($wxinfo['unionid'] && $uid){
			$sql = "UPDATE ".$this->db->prefix."weixin_user SET user_id='".$uid."' WHERE unionid='".$wxinfo['unionid']."'";
			$this->db->query($sql);
		}
		//登记推荐人关系
		if($voucher && $uid){
			$this->model('user')->save_relation($uid,$voucher);
		}
		$array = array('id'=>$uid,'user'=>$account,'pass'=>$password,'nickname'=>$wxinfo['nickname']);
		return $array;
	}

	private function _save_weixin($wxinfo,$source='')
	{
		$wxuser = array('headimg'=>$wxinfo['headimgurl'],'nickname'=>trim($wxinfo['nickname']));
		$wxuser['openid'] = $wxinfo['openid'];
		$wxuser['country'] = $wxinfo['country'];
		$wxuser['province'] = $wxinfo['province'];
		$wxuser['city'] = $wxinfo['city'];
		$wxuser['gender'] = $wxinfo['sex'];
		$wxuser['unionid'] = $wxinfo['unionid'];
		$wxuser['language'] = $wxinfo['language'];
		$wxuser['source'] = $source ? $source : '微信公众号';
		$chk = $this->model('weixin')->user_one($wxinfo['openid'],$wxuser['source']);
		if($chk){
			return $chk;
		}
		$insert_id = $this->model('weixin')->user_save($wxuser);//保存微信用户信息
		if(!$insert_id){
			return false;
		}
		$wxuser['id'] = $insert_id;
		return $wxuser;
	}

	/**
	 * 订阅通知
	**/
	private function _subscribe($rs)
	{
		$config = $this->model('weixin')->config_one('mp');
		$this->lib('weixin')->app_id($config['app_id']);
		$this->lib('weixin')->app_secret($config['app_secret']);
		$this->lib('weixin')->openid($rs['FromUserName']);
		$this->lib('weixin')->account($rs['ToUserName']);
		$wx_rs = $this->lib('weixin')->userinfo($rs['FromUserName']);
		if(!$wx_rs){
			$info = "欢迎关注公众号【".$this->site['title']."】，你的支持是我们最大的动力";
			$this->lib('weixin')->echo_xml($info);
			exit;
		}
		$voucher = 0;
		if($rs['EventKey'] && strpos($rs['EventKey'],'qrscene_') !== false){
			$voucher = trim(str_replace('qrscene_','',$rs['EventKey']));
		}
		$user = $this->_save_account($wx_rs,$voucher); //关注公众号即自动注册
		$welcome = $this->model('weixin')->config_welcome();
		if(!$welcome){
			$welcome = '您好，欢迎您光临【'.$this->site['title'].'】';
		}
		$wxconfig = $this->model('weixin')->config_subscribe();
		$color = $wxconfig && $wxconfig['color'] ? $wxconfig['color'] : '#000000';
		$list = array('[昵称]','[账号]','[密码]');
		$welcome = str_replace('[昵称]',$user['nickname'],$welcome);
		$welcome = str_replace('[账号]',$user['user'],$welcome);
		$welcome = str_replace('[密码]',$user['pass'],$welcome);
		$this->lib('weixin')->echo_xml($welcome);
		exit;
	}

	private function _to_back($uid)
	{
		$user = $this->model('user')->get_one($uid);
		$this->session->assign('user_id',$user['id']);
		$this->session->assign('user_gid',$user['group_id']);
		$this->session->assign('user_name',$user['user']);
		$backurl = $this->session->val("_back");
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		$this->_location($backurl);
	}

	/**
	 * 取消订阅操作，后台如果定义删除
	**/
	private function _unsubscribe($rs)
	{
		$openid = $rs['FromUserName'];
		$wxuser = $this->model('weixin')->user_one($openid);
		if(!$wxuser){
			echo '';
			exit;
		}
		$config_subscribe = $this->model('weixin')->config_subscribe();
		if($config_subscribe['is_del']){
			$uid = $wxuser['user_id'];
			$sql = "SELECT count(id) FROM ".$this->db->prefix."weixin_user WHERE user_id='".$uid."'";
			$count = $this->db->count($sql);
			$sql = "DELETE FROM ".$this->db->prefix."weixin_user WHERE id='".$wxuser['id']."'";
			$this->db->query($sql);
			if($count == 1){
				$this->model('user')->del($wxuser['user_id']);
			}
		}
		echo '';
		exit;
	}
	
	private function _valid()
	{
		$config = $this->model('weixin')->config_one('mp');
		$echoStr = $this->get('echostr');
        $signature = $this->get('signature');
        $timestamp = $this->get('timestamp');
        $nonce = $this->get('nonce');
        $token = $config['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
        }else{
	        echo $echoStr;
        }
        exit;
	}

}
