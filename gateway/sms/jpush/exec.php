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
		phpok_log(P_Lang('极光短信未配置参数'));
	}
	return false;
}
if(!$rs['ext']['app_key'] || !$rs['ext']['master_secret'] || !$rs['ext']['sign']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('极光短信未配置必填参数'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['title']){
	if($this->config['debug']){
		phpok_log(P_Lang('极光短信发送未指定接收手机号及发送的模板ID'));
	}
	return false;
}
$datalist = false;
if($extinfo['content']){
	$tmpcontent = explode("\n",$extinfo['content']);
	$tmp = false;
	foreach($tmpcontent as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp2 = explode(":",trim($value));
		if($tmp2[0] != '' && $tmp2[1] != ''){
			$tmp[$tmp2[0]] = $tmp2[1];
		}
	}
	if($tmp){
		$datalist = $tmp;
		unset($tmp);
	}
}
include_once('sms.php');
$client = new JSMS($rs['ext']['app_key'], $rs['ext']['master_secret']);
$info = $client->sendMessage($extinfo['mobile'], $extinfo['title'], $datalist, null,$rs['ext']['sign']);
if(!$info || !is_array($info)){
	if($this->config['debug']){
		phpok_log('短信发送失败');
	}
	return false;
}
if($info['body']){
	$t = $info['body'];
	if($t['error']){
		if($this->config['debug']){
			phpok_log($t['error']['code'].':'.$t['error']['message']);
		}
		return false;
	}
}
return true;