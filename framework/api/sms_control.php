<?php
/**
 * 短信验证码接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月04日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class sms_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
	}

	/**
	 * 获取验证码
	**/
	public function index_f()
	{
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->error(P_Lang('手机号不能为空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->error(P_Lang('手机号不符合格式要求'));
		}
		$tplid = $this->get('tplid','int');
		if(!$tplid){
			$tplid = $this->site['login_type_sms'];
			if(!$tplid){
				$this->error(P_Lang('未指定短信模板ID'));
			}
		}
		$code = $this->session->val('sms_code');
		if($code && strpos($code,'-') !== false){
			$tmp = explode('-',$code);
			$time = $tmp[1];
			$code = $tmp[0];
			$chktime = $this->time - 60;
			if($time && $time > $chktime){
				$this->error(P_Lang('验证码已发送，请等待一分钟后再获取'));
			}
		}
		$gateway = $this->get('gateway','int');
		if(!$gateway){
			$gateway = 'default';
		}
		$this->gateway('type','sms');
		$this->gateway('param',$gateway);
		if(!$this->gateway('check')){
			$this->error(P_Lang('网关参数信息未配置'));
		}
		$code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
		if(!$code){
			$this->error(P_Lang('网关配置错误，请联系工作人员'));
		}
		if($code['code']){
			foreach($code['code'] as $key=>$value){
				if($value['required'] && $value['required'] == 'true' && !$this->gateway['param']['ext'][$key]){
					$this->error(P_Lang('网关配置不完整，请联系工作人员'));
				}
			}
		}
		$tpl = $this->model('email')->tpl($tplid);
		if(!$tpl){
			$this->error(P_Lang('短信验证模板获取失败，请检查'));
		}
		if(!$tpl['content']){
			$this->error(P_Lang('短信模板内容为空，请联系管理员'));
		}
		$tplcontent = strip_tags($tpl['content']);
		if(!$tplcontent){
			$this->error(P_Lang('短信模板内容是空的，请联系管理员'));
		}
		$data = $this->model('vcode')->create('sms',4);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$info = $data['code'];
		$this->assign('code',$info);
		$this->assign('mobile',$mobile);
		$content = $this->fetch($tplcontent,'msg');
		$title = $this->fetch($tpl['title'],'msg');
		$this->session->assign('sms_code',$info.'-'.$this->time);
		$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'code'=>$info));
		$this->success();
	}

	/**
	 * 验证码验证
	**/
	public function check_f()
	{
		$code = $this->get('code');
		$check = $this->smscheck($code);
		if($check['status']){
			$this->success();
		}
		$this->error($check['info']);
	}

	/**
	 * 用于被调用的验证码
	 * @参数 $code 验证码
	**/
	public function smscheck($code='')
	{
		$data = array('status'=>false,'info'=>'');
		if(!$code){
			$data['info'] = P_Lang('验证码不能为空');
			return $data;
		}
		$chk_code = $this->session->val('sms_code');
		if(!$chk_code){
			$data['info'] = P_Lang('验证码没有记录，请重新获取');
			return $data;
		}
		if(strpos($chk_code,'-') !== true){
			$data['info'] = P_Lang('验证码记录有误，请重新获取');
			return $data;
		}
		$tmp = explode('-',$chk_code);
		$time = $tmp[1];
		if(!$time){
			$data['info'] = P_Lang('验证码记录有误，请重新获取');
			return $data;
		}
		$chk_code = $tmp[0];
		if(!$chk_code){
			$data['info'] = P_Lang('验证码记录有误，请重新获取');
			return $data;
		}
		$chktime = $this->time - 300;
		if($time < $chktime){
			$data['info'] = P_Lang('验证码已过期，请重新获取');
			return $data;
		}
		if($chk_code != $code){
			$data['info'] = P_Lang('验证码填写不正确，请修改或重新获取');
			return $data;
		}
		//验证码检测通过，清除 session 记录
		$this->session->unassign('sms_code');
		return array('status'=>true);
	}
}
