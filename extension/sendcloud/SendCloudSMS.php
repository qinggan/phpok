<?php
require_once 'util/HttpClient.php';
class SendCloudSMS {
	private $host = 'http://www.sendcloud.net/';
	private $sms_user;
	private $sms_key;
	private $version;
	private $client;
	public function __construct($sms_user, $sms_key, $version = 'v1') {
		$this->sms_user = $sms_user;
		$this->sms_key = $sms_key;
		$this->client = new HttpClient ( $this->host );
	}
	
	
	private function _getSignature($param) {
		$sParamStr = "";
		ksort ( $param );
		foreach ( $param as $sKey => $sValue ) {
			if (is_array ( $sValue )) {
				$value = implode ( ";", $sValue );
				$sParamStr .= $sKey . '=' . $value . '&';
			} else {
				$sParamStr .= $sKey . '=' . $sValue . '&';
			}
		}
		$sParamStr = trim ( $sParamStr, '&' );
		$sSignature = md5 ( $this->sms_key . "&" . $sParamStr . "&" . $this->sms_key );
		return $sSignature;
	}
	public function send(SmsMsg $sms) {
		$method = "POST";
		$param = $sms->jsonSerialize ();
		$param ['smsUser'] = $this->sms_user;
		$phone= $param['phone'];
		$param['phone']=implode(";", $phone);
		$param['vars']=json_encode($param['vars']);
		$param ['signature'] = $this->_getSignature ( $param );
		$resonse = $this->client->post ( 'POST', '/smsapi/send', '',$param );
		return $resonse;
	}
	public function sendVoice(VoiceMsg $sms) {
		$method = "POST";
		$param = $sms->jsonSerialize ();
		$param ['smsUser'] = $this->sms_user;
		$param ['signature'] = $this->_getSignature ( $param );
		$resonse = $this->client->post ( $method, '/smsapi/sendVoice','', $param );
		return $resonse;
	}
}



