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
	$charset = $rs['ext']['charset'] ? str_replace('-','',$rs['ext']['charset']) : 'utf8';
	$this->lib('email')->setting('smtp_charset',$charset);
	$this->lib('email')->setting('smtp_server',$rs['ext']['server']);
	$this->lib('email')->setting('smtp_port',($rs['ext']['port'] ? $rs['ext']['port'] : 25));
	$this->lib('email')->setting('smtp_ssl',(($rs['ext']['ssl'] && $rs['ext']["ssl"] == 'yes') ? true : false));
	$this->lib('email')->setting('smtp_user',$rs['ext']["account"]);
	$this->lib('email')->setting('smtp_pass',$rs['ext']["password"]);
	$this->lib('email')->setting('smtp_reply',$rs['ext']["email"]);
	$this->lib('email')->setting('smtp_admin',$rs['ext']["email"]);
	$fullname = $rs['ext']['fullname'] ? $rs['ext']['fullname'] : '';
	if(!$fullname){
		$tmp = strstr($rs['ext']["email"],'@');
		$fullname = str_replace($tmp,'',$rs['ext']["email"]);
	}
	$this->lib('email')->setting('smtp_fromname',$fullname);
	$fullname = str_replace(strstr($email,'@'),'',$email);
	$info = $this->lib('email')->send_mail($email,$title,$content,$fullname);
	if(!$info){
		$this->error($this->lib('email')->error());
		return false;
	}
	return true;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendemail.html','abs-file');