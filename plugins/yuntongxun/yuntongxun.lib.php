<?php
/*****************************************************************************************
	文件： plugins/yuntongxun/yuntongxun.lib.php
	备注： 云通讯短信发送类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月22日 14时07分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class yuntongxun_lib
{
	private $AccountSid;
	private $AccountToken;
	private $AppId;
	private $ServerIP;
	private $ServerPort;
	private $SoftVersion;
	private $Batch;  //时间戳
	private $BodyType = "json";//包体格式，可填值：json 、xml
	public function __construct($ServerIP,$ServerPort,$SoftVersion)
	{
		$this->Batch = date("YmdHis");
		$this->ServerIP = $ServerIP;
		$this->ServerPort = $ServerPort;
		$this->SoftVersion = $SoftVersion;
	}

	public function setAccount($AccountSid,$AccountToken)
	{
		$this->AccountSid = $AccountSid;
		$this->AccountToken = $AccountToken;
	}

	public function setAppId($AppId)
	{
		$this->AppId = $AppId;
	}

	public function curl_post($url,$data,$header,$post=1)
	{
		$ch = curl_init();
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, $post);
		if($data){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		$result = curl_exec ($ch);
		if($result == FALSE){
			if($this->BodyType=='json'){
				$result = '{"statusCode":"172001","statusMsg":"网络错误"}';
			} else {
				$result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
			}
		}
		curl_close($ch);
		return $result;
	}

	public function sendTemplateSMS($to,$datas,$tempId)
	{
		$auth=$this->accAuth();
		if($auth!=""){
			return $auth;
		}
		if($this->BodyType=="json"){
			$data="";
			for($i=0;$i<count($datas);$i++){
				$data = $data.'"'.$datas[$i].'",';
			}
			$body = '{"to":"'.$to.'","templateId":"'.$tempId.'","appId":"'.$this->AppId.'","datas":['.$data.']}';
			$body= "{'to':'$to','templateId':'$tempId','appId':'$this->AppId','datas':[".$data."]}";
		}else{
			$data="";
			for($i=0;$i<count($datas);$i++){
				$data = $data. "<data>".$datas[$i]."</data>";
			}
			$body='<TemplateSMS><to>'.$to.'</to><appId>'.$this->AppId.'</appId><templateId>'.$tempId.'</templateId><datas>'.$data.'</datas></TemplateSMS>';
		}
		$sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
		$url = 'https://'.$this->ServerIP.':'.$this->ServerPort.'/'.$this->SoftVersion.'/Accounts/'.$this->AccountSid.'/SMS/TemplateSMS?sig='.$sig;
		$authen = base64_encode($this->AccountSid . ":" . $this->Batch);
		$header = array('Accept:application/'.$this->BodyType,'Content-Type:application/'.$this->BodyType.';charset=utf-8','Authorization:'.$authen);
		$result = $this->curl_post($url,$body,$header);
		if($this->BodyType=="json"){
			$datas=json_decode($result);
		}else{
			$datas = simplexml_load_string(trim($result," \t\n\r"));
		}
		if($datas->statusCode==0){
			if($this->BodyType=="json"){
				$datas->TemplateSMS =$datas->templateSMS;
				unset($datas->templateSMS);
			}
		}
		return $datas;
	}

	private function accAuth()
	{
		if($this->ServerIP==""){
			$data = new stdClass();
			$data->statusCode = '172004';
			$data->statusMsg = 'IP为空';
			return $data;
		}
		if($this->ServerPort<=0){
			$data = new stdClass();
			$data->statusCode = '172005';
			$data->statusMsg = '端口错误（小于等于0）';
			return $data;
		}
		if($this->SoftVersion==""){
			$data = new stdClass();
			$data->statusCode = '172013';
			$data->statusMsg = '版本号为空';
			return $data;
		}
		if($this->AccountSid==""){
			$data = new stdClass();
			$data->statusCode = '172006';
			$data->statusMsg = '主帐号为空';
			return $data;
		}
		if($this->AccountToken==""){
			$data = new stdClass();
			$data->statusCode = '172007';
			$data->statusMsg = '主帐号令牌为空';
			return $data;
		}
		if($this->AppId==""){
			$data = new stdClass();
			$data->statusCode = '172012';
			$data->statusMsg = '应用ID为空';
			return $data;
		}
	}
}
?>