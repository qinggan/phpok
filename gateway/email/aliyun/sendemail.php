<?php
/**
 * SMTP发送邮件
 * @package phpok\gateway\email\smtp
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$update = $this->get('update','int');
if($update){
	$title = $this->get('title');
	if(!$title){
		$this->error('邮件主题不能为空');
		return false;
	}
	$email = $this->get('email');
	if(!$email){
		$this->error('目标Email不能为空');
		return false;
	}
	if(!$this->lib('common')->email_check($email)){
		$this->error('Email格式不正确');
		return false;
	}
	$content = $this->get('content','html');
	if(!$content){
		$this->error('邮件内容不能为空');
		return false;
	}

	$this->lib('aliyun')->regoin_id($rs['ext']['server']);
	$this->lib('aliyun')->access_key($rs['ext']['appkey']);
	$this->lib('aliyun')->access_secret($rs['ext']['appsecret']);
	$this->lib('aliyun')->signature($rs['ext']['signame']);
	$this->lib('aliyun')->dm_account($rs['ext']['email']);
	$this->lib('aliyun')->dm_name($rs['ext']['nickname']);
	$info = $this->lib('aliyun')->email($title,$content,$email);
	if(!$info){
		$this->error('发送失败');
		return false;
	}
	if(!$info['status']){
		$error = $info['errid'] ? $info['errid'].":".$info['error'] : $info['error'];
		$this->error($error);
		return false;
	}
	$this->success();
	return true;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendemail.html','abs-file');