<?php
/***********************************************************
	Filename: libs/system/email.php
	Note	: 发送邮件类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
//引入phpmail控件发送邮件
class email_lib
{
	var $app;
	var $tpl;
	var $timeout = 15;
	var $smtp_charset = "utf8";
	var $smtp_server = "";
	var $smtp_port = 25;
	var $smtp_ssl = 0;
	var $smtp_user = "";
	var $smtp_pass = "";
	var $smtp_reply = "";
	var $smtp_admin = "";
	var $smtp_to = "";
	var $smtp_fromname = "Webmaster";
	var $smtp;
	var $is_debug = false;
	var $obj;

	//读取邮件信息
	function __construct()
	{
		include_once($GLOBALS['app']->dir_phpok."engine/phpmailer/class.phpmailer.php");
		//初始化邮件服务器参数
		$this->smtp_charset = ($GLOBALS['app']->site["email_charset"] == "gbk" && function_exists("iconv")) ? "gbk" : "utf8";
		$this->smtp_server = $GLOBALS['app']->site["email_server"];
		$this->smtp_port = $GLOBALS['app']->site["email_port"] ? $GLOBALS['app']->site["email_port"] : 25;
		$this->smtp_ssl = $GLOBALS['app']->site["email_ssl"] ? true : false;
		$this->smtp_user = $GLOBALS['app']->site["email_account"];
		$this->smtp_pass = $GLOBALS['app']->site["email_pass"];
		$this->smtp_reply = $GLOBALS['app']->site["email"] ? $GLOBALS['app']->site["email"] : $GLOBALS['app']->site["email"];
		$this->smtp_admin = $GLOBALS['app']->site["email"];
		$this->smtp_fromname = $GLOBALS['app']->site["email_name"];
		if(!$this->smtp_fromname){
			$tmp = strstr($this->smtp_admin,'@');
			$this->smtp_fromname = str_replace($tmp,'',$this->smtp_admin);
		}
	}

	function set_debug($debug = false)
	{
		$this->is_debug = $debug;
	}

	function setting($var,$val)
	{
		if($var && $val){
			$this->$var = $val;
		}
	}

	//通知管理员
	//
	function send_admin($title,$content,$account)
	{
		if(!$title || !$content || !$account || !is_array($account)){
			return false;
		}
		if(!$this->smtp_server || !$this->smtp_user || !$this->smtp_pass){
			return false;
		}
		$this->smtp();
		if($this->obj->CharSet != "utf8"){
			$title = $GLOBALS['app']->lib('string')->charset($title,"UTF-8","GBK");
			$content = $GLOBALS['app']->lib('string')->charset($content,"UTF-8","GBK");
			$this->obj->FromName = $GLOBALS['app']->lib('string')->charset($this->obj->FromName,"UTF-8","GBK");
		}
		$this->obj->Subject = $title;
		$this->obj->MsgHTML($content);
		foreach($account as $key=>$value){
			//如果管理员邮箱和要发送的邮箱是一样的
			if($this->smtp_admin == $value['email']){
				continue;
			}
			if(!$this->obj->CharSet != 'utf8'){
				$value['account'] = $GLOBALS['app']->lib('string')->charset($value['account'],'UTF-8','GBK');
			}
			$this->obj->AddAddress($value['email'],$value['account']);
		}
		return $this->obj->Send();
	}

	function smtp()
	{
		$this->obj = new PHPMailer();
		$this->obj->CharSet = $this->smtp_charset;
		$this->obj->IsSMTP();
		$this->obj->SMTPAuth = true;
		$this->obj->SMTPDebug = $this->is_debug;//是否启用调试
		$this->obj->IsHTML(true);
		$this->obj->Username = trim($this->smtp_user);
		$this->obj->Password = trim($this->smtp_pass);
		$this->obj->Host = trim($this->smtp_server);
		$this->obj->Port = $this->smtp_port;
		if($this->smtp_ssl){
			$this->obj->SMTPSecure = 'ssl';
		}
		$this->obj->LE = "\r\n";
		$this->obj->Timeout = 15;
		//发件人
		$this->obj->From = $this->smtp_admin;
		$this->obj->FromName = $this->smtp_fromname;
	}

	//连接到email环境中
	function send_mail($sendto,$subject,$content,$user_name="")
	{
		if(!$subject || !$content || !$sendto){
			return false;
		}
		if(!$this->smtp_server || !$this->smtp_user || !$this->smtp_pass){
			return false;
		}
		$this->smtp();
		if($this->obj->CharSet != "utf8"){
			$subject = $GLOBALS['app']->lib('string')->charset($subject,"UTF-8","GBK");
			$content = $GLOBALS['app']->lib('string')->charset($content,"UTF-8","GBK");
			$this->obj->FromName = $GLOBALS['app']->lib('string')->charset($this->obj->FromName,"UTF-8","GBK");
		}
		$this->obj->Subject = $subject;
		$this->obj->MsgHTML($content);
		$sendto_array = explode(";",$sendto);
		if(count($sendto_array)<2){
			if(!$user_name){
				$user_name = str_replace(strstr($sendto,"@"),"",$sendto);
			}
			if($this->obj->CharSet != "utf8"){
				 $user_name = $GLOBALS['app']->lib('string')->charset($user_name,"UTF-8","GBK");
			}
			$this->obj->AddAddress($sendto,$user_name);
		}else{
			foreach($sendto_array AS $key=>$value){
				$v_name = str_replace(strstr($value,"@"),"",$value);
				$this->obj->AddAddress($value,$v_name);
			}
		}
		return $this->obj->Send();
	}

}
?>