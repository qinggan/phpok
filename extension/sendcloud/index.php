<?php
/**
 * SendCloud提供的云服务
 * @package phpok\extension\sendcloud
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年02月27日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class sendcloud_lib
{
	private $api_user = '';
	private $api_key = '';
	private $label_id = 14411;

	private $email_from = '';
	private $email_name = 'PHPOK';

	private $sms_template_id = 0;
	
	public function __construct()
	{
		require_once(ROOT.'extension/sendcloud/util/HttpClient.php');
		require_once(ROOT.'extension/sendcloud/util/Mimetypes.php');
		require_once(ROOT.'extension/sendcloud/util/Mail.php');
		require_once(ROOT.'extension/sendcloud/SendCloud.php');
		require_once(ROOT.'extension/sendcloud/SendCloudSMS.php');
		require_once(ROOT.'extension/sendcloud/util/SMS.php');
	}

	public function api_user($val='')
	{
		if($val){
			$this->api_user = $val;
		}
		return $this->api_user;
	}

	public function api_key($val='')
	{
		if($val){
			$this->api_key = $val;
		}
		return $this->api_key;
	}

	public function email_from($val='')
	{
		if($val){
			$this->email_from = $val;
		}
		return $this->email_from;
	}

	public function email_name($val='')
	{
		if($val){
			$this->email_name = $val;
		}
		return $this->email_name;
	}

	public function label_id($val='')
	{
		if($val){
			$this->label_id = $val;
		}
		return $this->label_id;
	}

	/**
	 * 发送邮件
	 * @参数 $title 标题
	 * @参数 $content 内容
	 * @参数 $emailto 目标邮箱
	 * @返回 false 或 数组
	**/
	public function email($title='',$content='',$emailto='')
	{
		if(!$title || !$content || !$emailto){
			return false;
		}
		$sendcloud = new SendCloud($this->api_user, $this->api_key,'v2');
		$mail=new Mail();
		$mail->setFrom($this->email_from);
		$mail->addTo($emailto);
		$mail->setFromName($this->email_name);
		$mail->setSubject($title);
		$mail->setContent(stripslashes($content));
		$mail->setRespEmailId(true);
		$mail->setLabel($this->label_id);
		$info = $sendcloud->sendCommon($mail);
		//phpok_log($mail);
		//phpok_log($info.'/////');
		if(!$info){
			return false;
		}
		return json_decode($info,true);
	}

	public function sms_template_id($val='')
	{
		if($val){
			$this->sms_template_id = $val;
		}
		return $this->sms_template_id;
	}

	
	/**
	 * 发送短信
	 * @参数 $mobile 目标手机号
	 * @参数 $data 数组，要传递的参数
	 * @返回 数组
	**/
	public function sms($mobile='',$data='')
	{
		if(!$mobile){
			return false;
		}
		if(!$data || !is_array($data)){
			return false;
		}
		if(is_string($data)){
			$data = unserialize($data);
		}
		$sendSms=new SendCloudSMS($this->api_user, $this->api_key);
		$smsMsg=new SmsMsg();
		$smsMsg->addPhoneList(array($mobile));
		foreach($data as $key=>$value){
			$smsMsg->addVars($key,$value);
		}
		$smsMsg->setTemplateId($this->sms_template_id);
		$smsMsg->setTimestamp(time());
		$resonse= $sendSms->send($smsMsg);
		$info = $resonse->body();
		if(!$info){
			return false;
		}
		return json_decode($info,true);
	}
}
