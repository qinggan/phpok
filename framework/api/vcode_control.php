<?php
/**
 * 验证码接口
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
		$width = $this->get('width','int');
		if(!$width || $width < 0){
			$width = 76;
		}
		$height = $this->get('height','int');
		if(!$height || $height < 0){
			$height = 24;
		}
		$info = $this->lib("vcode")->word();
		$code = md5(strtolower($info));
		$this->session->assign('vcode',$code);
		$this->lib('vcode')->width($width);
		$this->lib('vcode')->height($height);
		$this->lib('vcode')->font($this->dir_data.'font/roboto-black.ttf');
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
		$chkfile = $this->dir_cache."".md5($mobile).".php";
		if(file_exists($chkfile)){
			$chktime = $this->lib('file')->cat($chkfile);
			if(($chktime+60) > $this->time){
				$this->error(P_Lang('验证码已发送，请等待一分钟后再获取'));
			}
		}
		$tplid = $this->get('tplid','int');
		$tpl_type = 'number';
		if(!$tplid){
			$tplid = $this->site['login_type_sms'];
			if(!$tplid){
				$this->error(P_Lang('未配置短信验证码模板'));
			}
			$tpl_type = 'code';
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
		$act = $this->get('act');
		if(!$act){
			$act = 'login';
		}
		if($act == 'login' || $act == 'register'){
			$user = $this->model('user')->user_mobile($mobile);
			if(!$user && $act == 'login'){
				$this->error(P_Lang('手机号未注册'));
			}
			if($user && $act == 'register'){
				$this->error(P_Lang('手机号已注册'));
			}
		}
		$data = $this->model('vcode')->create('sms',4);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->session->assign('vcode2mobile',$mobile);
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
		$tpl = $tpl_type == 'code' ? $this->model('email')->tpl($tplid) : $this->model('email')->get_one($tplid);
		if(!$tpl){
			$this->error(P_Lang('短信模板不存在'));
		}
		$this->assign('code',$data['code']);
		$this->assign('mobile',$mobile);
		$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
		if($content){
			$content = strip_tags($content);
		}
		$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
		$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
		$this->lib('file')->vi($this->time,$chkfile);
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
		$chkfile = $this->dir_cache."".md5($email).".php";
		if(file_exists($chkfile)){
			$chktime = $this->lib('file')->cat($chkfile);
			if(($chktime+60) > $this->time){
				$this->error(P_Lang('验证码已发送，请等待一分钟后再获取'));
			}
		}
		$tplid = $this->get('tplid','int');
		$tpl_type = 'number';
		if(!$tplid){
			$tplid = $this->site['login_type_email'];
			$tpl_type = 'code';
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
		$act = $this->get('act');
		if(!$act){
			$act = 'login';
		}
		if($act == 'login' || $act == 'register'){
			$user = $this->model('user')->user_email($email);
			if(!$user && $act == 'login'){
				$this->error(P_Lang('邮箱未注册'));
			}
			if($user && $act == 'register'){
				$this->error(P_Lang('邮箱已注册'));
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
		$data = $this->model('vcode')->create('email',6);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->session->assign('vcode2email',$email);
		$tpltitle = P_Lang('获取验证码');
		$tplcontent = P_Lang('您的验证码是：').'{$code}';
		if($tplid){
			$tpl = $tpl_type == 'code' ? $this->model('email')->tpl($tplid) : $this->model('email')->get_one($tplid);
			if($tpl && $tpl['content'] && strip_tags($tpl['content'])){
				$tplcontent = $tpl['content'];
			}
			if($tpl && $tpl['title']){
				$tpltitle = $tpl['title'];
			}
		}
		$this->assign('code',$data['code']);
		$this->assign('email',$email);
		$title = $this->fetch($tpltitle,'msg');
		$content = $this->fetch($tplcontent,'msg');
		$info = $this->gateway('exec',array('email'=>$email,'content'=>$content,'title'=>$title));
		if(!$info){
			$this->error(P_Lang('邮件发送失败，请检查'));
		}
		$this->lib('file')->vi($this->time,$chkfile);
		$this->success();
	}
}