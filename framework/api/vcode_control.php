<?php
/**
 * 验证码接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 图形验证码
	**/
	public function index_f()
	{
		$info = $this->lib("vcode")->word();
		$code = md5(strtolower($info));
		$this->session->assign('vcode',$code);
		$this->lib("vcode")->create();
	}

	/**
	 * 短信验证码
	 * @参数 mobile 手机号，目前仅限中国大陆手机号有效
	 * @参数 tplid 验证码模板ID，未设置使用后台设置的验证码模板ID
	 * @参数 gateid 短信网关ID，未设置使用默认的网关
	**/
	public function sms_f()
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
				$this->error(P_Lang('未配置短信验证码模板'));
			}
		}
		$gateid = $this->get('gateid','int');
		if($gateid){
			$rs = $this->model('gateway')->get_one($gateid,'sms',true);
		}
		if(!$rs){
			$rs = $this->model('gateway')->get_default('sms');
		}
		if(!$rs){
			$this->error(P_Lang('没有安装短信发送引挈，请先安装并设置默认'),$backurl);
		}
		$vcode = $this->session->val('vcode_sms');
		$time = $this->session->val('vcode_time');
		if($vcode && $time){
			if( ($time + 60) > $this->time){
				$this->error(P_Lang('禁止频繁发送验证码，请于一分钟后请求'));
			}
		}
		$this->gateway('type','sms');
		$this->gateway('param',$rs['id']);
		if(!$this->gateway('check')){
			$this->error(P_Lang('网关参数信息未配置'));
		}
		$code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
		if(!$code){
			$this->error(P_Lang('网关配置错误，请联系工作人员'));
		}
		if($code['code']){
			$error = false;
			foreach($code['code'] as $key=>$value){
				if($value['required'] && $value['required'] == 'true' && $this->gateway['param']['ext'][$key] == ''){
					$error = true;
					break;
				}
			}
			if($error){
				$this->error(P_Lang('网关配置不完整，请联系工作人员'));
			}
		}
		$tpl = $this->model('email')->tpl($tplid);
		if(!$tpl){
			$this->error(P_Lang('短信模板不存在'));
		}
		$info = $this->lib("vcode")->word();
		$this->session->assign('vcode_sms',$info);
		$this->session->assign('vcode_time',$this->time);
		$this->assign('code',$info);
		$this->assign('mobile',$mobile);
		$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
		if($content){
			$content = strip_tags($content);
		}
		$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
		$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
		$this->success();
	}

	/**
	 * 邮件验证码
	 * @参数 email 邮箱
	 * @参数 tplid 验证码模板ID，未设置使用后台设置的验证码模板ID
	 * @参数 gateyid 网关ID，未设置使用默认的网关
	**/
	public function email_f()
	{
		$email = $this->get('email');
		if(!$email){
			$this->error(P_Lang('Email不能为空'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->error(P_Lang('Email地址不符合要求'));
		}
		$tplid = $this->get('tplid','int');
		if(!$tplid){
			$tplid = $this->site['login_type_email'];
		}
		$gateid = $this->get('gateid','int');
		if($gateid){
			$rs = $this->model('gateway')->get_one($gateid,'email',true);
		}
		if(!$rs){
			$rs = $this->model('gateway')->get_default('email');
		}
		if(!$rs){
			$this->error(P_Lang('没有安装邮件发送引挈，请先安装并设置默认'),$backurl);
		}
		$vcode = $this->session->val('vcode_email');
		$time = $this->session->val('vcode_time');
		if($vcode && $time){
			if( ($time + 300) > $this->time){
				$this->error(P_Lang('禁止频繁发送验证码，请于五分钟后请求'));
			}
		}
		$this->gateway('type','email');
		$this->gateway('param',$rs['id']);
		if(!$this->gateway('check')){
			$this->error(P_Lang('网关参数信息未配置'));
		}
		$code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
		if(!$code){
			$this->error(P_Lang('网关配置错误，请联系工作人员'));
		}
		if($code['code']){
			$error = false;
			foreach($code['code'] as $key=>$value){
				if($value['required'] && $value['required'] == 'true' && $this->gateway['param']['ext'][$key] == ''){
					$error = true;
					break;
				}
			}
			if($error){
				$this->error(P_Lang('网关配置不完整，请联系工作人员'));
			}
		}
		$tpltitle = P_Lang('获取验证码');
		$tplcontent = P_Lang('您的验证码是：').'{$code}';
		if($tplid){
			$tpl = $this->model('email')->tpl($tplid);
			if($tpl && $tpl['content'] && strip_tags($tpl['content'])){
				$tplcontent = $tpl['content'];
			}
			if($tpl && $tpl['title']){
				$tpltitle = $tpl['title'];
			}
		}
		$info = $this->lib("vcode")->word();
		$this->assign('code',$info);
		$this->assign('email',$email);
		$title = $this->fetch($tpltitle,'msg');
		$content = $this->fetch($tplcontent,'msg');
		$this->gateway('exec',array('email'=>$email,'content'=>$content,'title'=>$title));
		$this->session->assign('vcode_email',$info);
		$this->session->assign('vcode_time',$this->time);
		$this->success();
	}
}