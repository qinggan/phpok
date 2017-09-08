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
	private $app;
	public $tpl;
	public $timeout = 15;
	public $smtp_charset = "utf8";
	public $smtp_server = "";
	public $smtp_port = 25;
	public $smtp_ssl = 0;
	public $smtp_user = "";
	public $smtp_pass = "";
	public $smtp_reply = "";
	public $smtp_admin = "";
	public $smtp_to = "";
	public $smtp_fromname = "Webmaster";
	public $smtp;
	public $is_debug = false;
	public $obj;

	//读取邮件信息
	public function __construct()
	{
		global $app;
		include_once($app->dir_root."gateway/email/class.phpmailer.php");
		$app->gateway('type','email');
		$app->gateway('param','default');
		$info = $app->gateway['param'];
		if(!$info){
			return false;
		}
		$info = $info['ext'];
		if(!$info){
			return false;
		}
		//初始化邮件服务器参数
		$this->smtp_charset = $info['charset'] ? str_replace('-','',$info['charset']) : 'utf8';
		$this->smtp_server = $info["server"];
		$this->smtp_port = $info["port"] ? $info["port"] : 25;
		$this->smtp_ssl = ($info['ssl'] && $info["ssl"] == 'yes') ? true : false;
		$this->smtp_user = $info["account"];
		$this->smtp_pass = $info["password"];
		$this->smtp_reply = $info["email"];
		$this->smtp_admin = $info["email"];
		$this->smtp_fromname = $info["fullname"];
		if(!$this->smtp_fromname){
			$tmp = strstr($this->smtp_admin,'@');
			$this->smtp_fromname = str_replace($tmp,'',$this->smtp_admin);
		}
	}

	public function set_debug($debug = false)
	{
		$this->is_debug = $debug;
	}

	public function setting($var,$val)
	{
		if($var && $val){
			$this->$var = $val;
		}
	}

	public function send_admin($title,$content,$account)
	{
		if(!$title || !$content || !$account || !is_array($account)){
			return false;
		}
		if(!$this->smtp_server || !$this->smtp_user || !$this->smtp_pass){
			return false;
		}
		global $app;
		$this->smtp();
		if($this->obj->CharSet != "utf8" && $this->obj->CharSet != 'utf-8'){
			$title = $app->lib('string')->charset($title,"UTF-8","GBK");
			$content = $app->lib('string')->charset($content,"UTF-8","GBK");
			$this->obj->FromName = $app->lib('string')->charset($this->obj->FromName,"UTF-8","GBK");
		}
		$this->obj->Subject = $title;
		$this->obj->MsgHTML(stripslashes($content));
		foreach($account as $key=>$value){
			//如果管理员邮箱和要发送的邮箱是一样的
			if($this->smtp_admin == $value['email']){
				continue;
			}
			if(!$this->obj->CharSet != 'utf8' && $this->obj->CharSet != 'utf-8'){
				$value['account'] = $app->lib('string')->charset($value['account'],'UTF-8','GBK');
			}
			$this->obj->AddAddress($value['email'],$value['account']);
		}
		return $this->obj->Send();
	}

	public function error()
	{
		return $this->obj->ErrorInfo;
	}

	public function smtp()
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
	public function send_mail($sendto,$subject,$content,$user_name="")
	{
		if(!$subject || !$content || !$sendto){
			return false;
		}
		if(!$this->smtp_server || !$this->smtp_user || !$this->smtp_pass){
			return false;
		}
		global $app;
		$this->smtp();
		if($this->obj->CharSet != "utf8" && $this->obj->CharSet != 'utf-8'){
			$subject = $app->lib('string')->charset($subject,"UTF-8","GBK");
			$content = $app->lib('string')->charset($content,"UTF-8","GBK");
			$this->obj->FromName = $app->lib('string')->charset($this->obj->FromName,"UTF-8","GBK");
		}
		$this->obj->Subject = $subject;
		$this->obj->MsgHTML(stripslashes($content));
		$sendto_array = explode(";",$sendto);
		if(count($sendto_array)<2){
			if(!$user_name){
				$user_name = str_replace(strstr($sendto,"@"),"",$sendto);
			}
			if($this->obj->CharSet != "utf8" && $this->obj->CharSet != 'utf-8'){
				 $user_name = $app->lib('string')->charset($user_name,"UTF-8","GBK");
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