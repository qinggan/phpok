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
if(!$rs['ext'] || !$rs['ext']['password'] || !$rs['ext']['account']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['content']){
	if($this->config['debug']){
		phpok_log(print_r($extinfo,true));
	}
	return false;
}

//口令生成
$time = date("YmdHis",$this->time);
$sign = strtoupper(md5($rs['ext']['account'].$rs['ext']['password'].$time));
//
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "https://app.cloopen.com:8883";
$url.= '/'.$rs['ext']['softVersion'].'/Accounts/'.$rs['ext']['account'].'/SMS/TemplateSMS?sig='.$sign;
//设置Header
$jlist = array();
$jlist['to'] = $extinfo['mobile'];
$jlist['appId'] = $rs['ext']['appId'];
$jlist['templateId'] = $extinfo['title'];
if($extinfo['content']){
	$jlist['datas'] = explode("\n",$extinfo['content']);
}
$post_data = $this->lib('json')->encode($jlist);
$this->lib('curl')->is_post(true);
$this->lib('curl')->set_header('Accept','application/json');
$this->lib('curl')->set_header('Content-Type','application/json;charset=utf-8');
$this->lib('curl')->set_header('Authorization',base64_encode($rs['ext']['account'].':'.$time));
$this->lib('curl')->post_data($post_data);
$info = $this->lib('curl')->get_content($url);
if(!$info){
	phpok_log('短信发送失败');
	return false;
}
$info = $this->lib('json')->decode($info);
if($info['statusCode'] == '000000'){
	return true;
}
$errInfo = $this->lib('file')->cat($this->dir_gateway.'sms/'.$rs['code'].'/error.json');
$errInfo = $this->lib('json')->decode($errInfo);
if($errInfo[$info['statusCode']]){
	phpok_log($info['statusCode'].':'.$errInfo[$info['statusCode']]);
	return false;
}
phpok_log('短信发送失败');
return false;