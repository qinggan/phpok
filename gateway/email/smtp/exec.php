<?php
/**
 * 邮件发送
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年8月28日
**/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

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
$email = new PHPMailer(true);
$email->CharSet = ($rs['ext']['charset'] && $rs['ext']['charset'] == 'gbk') ? 'gbk' : 'utf-8';
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
$email->Timeout = 15;
//发件人
$email->From = $rs['ext']['email'];
$email->FromName = $rs['ext']['fullname'];
if($email->CharSet != "utf-8"){
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