<?php
/*****************************************************************************************
	文件： gateway/email/smtp/exec.php
	备注： 发送邮件
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月09日 17时08分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$rs['ext'] || !$rs['ext']['server'] || !$rs['ext']['account'] || !$rs['ext']['password'] || !$rs['ext']['email']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
if(!$extinfo['title'] || !$extinfo['content'] || !$extinfo['email']){
	if($this->config['debug']){
		phpok_log(print_r($extinfo,true));
	}
	return false;
}
include_once($this->dir_root."gateway/email/class.phpmailer.php");
$email = new PHPMailer();
$email->CharSet = ($rs['ext']['charset'] && $rs['ext']['charset'] == 'gbk') ? 'gbk' : 'utf8';
$email->IsSMTP();
$email->SMTPAuth = true;
$email->SMTPDebug = false;//是否启用调试
$email->IsHTML(true);
$email->Username = trim($rs['ext']['account']);
$email->Password = trim($rs['ext']['password']);
$email->Host = trim($rs['ext']['server']);
$email->Port = $rs['ext']['port'] ? $rs['ext']['port'] : 25;
if($rs['ext']['ssl'] == 'yes'){
	$email->SMTPSecure = 'ssl';
}
$email->LE = "\r\n";
$email->Timeout = 15;
//发件人
$email->From = $rs['ext']['email'];
$email->FromName = $rs['ext']['fullname'];
if($email->CharSet != "utf8"){
	$extinfo['title'] = $this->lib('string')->charset($extinfo['title'],"UTF-8","GBK");
	$extinfo['content'] = $this->lib('string')->charset($extinfo['content'],"UTF-8","GBK");
	$email->FromName = $this->lib('string')->charset($email->FromName,"UTF-8","GBK");
}
$email->Subject = $extinfo['title'];
$email->MsgHTML(stripslashes($extinfo['content']));
$email->AddAddress($extinfo['email'],$extinfo['fullname']);
$obj = $email->Send();
if(!$obj && $email->ErrorInfo){
	if($this->config['debug']){
		phpok_log($email->ErrorInfo);
	}
	return false;
}
return true;
?>