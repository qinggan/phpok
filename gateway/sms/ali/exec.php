<?php
/*****************************************************************************************
	文件： gateway/sms/duanxincm/exec.php
	备注： 执行操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月09日 16时43分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$rs['ext']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里短信未配置参数'));
	}
	return false;
}
if(!$rs['ext']['appcode'] && !$rs['ext']['appkey'] && !$rs['ext']['appsecret']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('阿里短信未配置必填参数'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['content']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里短信发送未指定接收手机号及发送内容'));
	}
	return false;
}
$type = strpos($extinfo['content'],'验证')
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://sms.market.alicloudapi.com/singleSendSms";
$data = array(
	'action'=>'send',
	'username'=>$rs['ext']['account'],
	'password'=>strtolower(md5($rs['ext']['password'])),
	'phone'=>$extinfo['mobile'],
	'content'=>$extinfo['content'],
	'encode'=>'utf8'
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
if($info != '100'){
	if($this->config['debug']){
		phpok_log($info);
	}
	return false;
}
return true;