<?php
/**
 * SMTP发送邮件
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月17日
**/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

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
	$mail = new PHPMailer(true);
	$mail->CharSet = ($rs['ext']['charset'] && $rs['ext']['charset'] == 'gbk') ? 'gbk' : 'utf-8';
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->SMTPDebug = false;//是否启用调试
	$mail->IsHTML(true);
	$mail->Username = trim($rs['ext']['account']);
	$mail->Password = trim($rs['ext']['password']);
	$mail->Host = trim($rs['ext']['server']);
	$mail->Port = $rs['ext']['port'] ? $rs['ext']['port'] : 25;
	$mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
	if($rs['ext']['ssl'] == 'yes'){
		$mail->SMTPSecure = 'ssl';
	}

	$mail->Timeout = 15;
	if($rs['ext']['reply']){
		$mail->AddReplyTo($rs['ext']["reply"],$rs['ext']['reply_name']);
	}else{
		$mail->AddReplyTo($rs['ext']["email"],$fullname);
	}
	if($rs['ext']['fullname']){
		$mail->FromName = $rs['ext']['fullname'];
	}
	$mail->From = $rs['ext']['email'];
	if($mail->CharSet != "utf-8"){
		$title = $this->lib('string')->charset($title,"UTF-8","GBK");
		$content = $this->lib('string')->charset($content,"UTF-8","GBK");
		if($rs['ext']['fullname']){
			$mail->FromName = $this->lib('string')->charset($mail->FromName,"UTF-8","GBK");
		}
	}
	$mail->Subject = $title;
	$mail->MsgHTML(stripslashes($content));
	$fullname = $rs['ext']['fullname'];
	if(!$fullname){
		$fullname = str_replace(strstr($email,"@"),"",$email);
	}
	$mail->AddAddress($email,$fullname);
	$obj = $mail->Send();
	if(!$obj && $mail->ErrorInfo){
		$this->error($mail->ErrorInfo);
		return false;
	}
	return true;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendemail.html','abs-file');