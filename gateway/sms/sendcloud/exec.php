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
		phpok_log(P_Lang('SendCloud短信未配置参数'));
	}
	return false;
}
if(!$rs['ext']['api_user'] && !$rs['ext']['api_key']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('SendCloud短信未配置必填参数'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['title']){
	if($this->config['debug']){
		phpok_log(P_Lang('SendCloud短信发送未指定接收手机号及发送的模板ID'));
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
//phpok_log(print_r($rs,true));
//phpok_log(print_r($extinfo,true));
$this->lib('sendcloud')->api_user($rs['ext']['api_user']);
$this->lib('sendcloud')->api_key($rs['ext']['api_key']);
$this->lib('sendcloud')->sms_template_id($extinfo['title']);
$info = $this->lib('sendcloud')->sms($extinfo['mobile'],$datalist);
if(!$info){
	if($this->config['debug']){
		phpok_log('短信发送失败');
	}
	return false;
}
if(!$info['result']){
	if($this->config['debug']){
		phpok_log($info['statusCode'].':'.$info['message']);
	}
	return false;
}
return true;