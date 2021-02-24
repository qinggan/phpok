<?php
/**
 * OK官网提供的邮件发送
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年10月9日
**/


/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$update = $this->get('update','int');
if($update){
	if(!$rs['ext']){
		$this->error('参数未配置完整');
	}
	if(!$rs['ext']['server'] || !$rs['ext']['app_id'] || !$rs['ext']['app_key']){
		$this->error('参数不完整');
	}
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('手机号不能为空');
		return false;
	}
	$code = $this->get('code');
	if(!$code){
		$this->error('验证码不能为空');
		return false;
	}
	if(!$this->lib('common')->tel_check($mobile)){
		$this->error('手机号格式不正确');
		return false;
	}
	$this->lib('phpok')->server_url($rs['ext']['server']);
	if($rs['ext'] && $rs['ext']['ip']){
		$this->lib('phpok')->ip($rs['ext']['ip']);
	}
	$this->lib('phpok')->app_id($rs['ext']['app_id']);
	$this->lib('phpok')->app_key($rs['ext']['app_key']);
	$data = array('code'=>$code,'mobile'=>$mobile);
	$t = $this->lib('phpok')->content($data);
	if(!$t){
		$this->error('发送失败');
	}
	if($t && !$t['status']){
		$this->error($t['info']);
	}
	$this->success($t['info']);
	return true;
}
$this->view($this->dir_root.'gateway/sms/oksms/sendsms.html','abs-file');