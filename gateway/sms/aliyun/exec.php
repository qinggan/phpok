<?php
/**
 * 阿里云短信发送
 * @package phpok\gateway\sms\aliyun
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2017 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年02月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!$rs['ext']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里短信未配置参数'));
	}
	return false;
}
if(!$rs['ext']['signame'] && !$rs['ext']['appkey'] && !$rs['ext']['appsecret'] && !$rs['ext']['server']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('阿里短信未配置必填参数'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['title']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里短信发送未指定接收手机号及发送的模板标签'));
	}
	return false;
}

$postdata = false;
if($extinfo['content']){
	$tmpcontent = explode("\n",$extinfo['content']);
	$tmp = false;
	foreach($tmpcontent as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp2 = explode(":",trim($value));
		if($tmp2[0] && $tmp2[1]){
			$tmp[$tmp2[0]] = $tmp2[1];
		}
	}
	if($tmp && is_array($tmp)){
		$postdata = $tmp;
		unset($tmp);
	}
}
$this->lib('aliyun')->end_point($rs['ext']['server']);
$this->lib('aliyun')->regoin_id($rs['ext']['regoin_id']);
$this->lib('aliyun')->access_key($rs['ext']['appkey']);
$this->lib('aliyun')->access_secret($rs['ext']['appsecret']);
$this->lib('aliyun')->signature($rs['ext']['signame']);
$this->lib('aliyun')->template_id($extinfo['title']);
$info = $this->lib('aliyun')->sms($extinfo['mobile'],$postdata);
if(!$info){
	if($this->config['debug']){
		phpok_log(P_Lang('短信发送失败'));
	}
	return false;
}
if(!$info['status']){
	$error = $info['errid'] ? $info['errid'].":".$info['error'] : $info['error'];
	if($this->config['debug']){
		phpok_log($error);
	}
	return false;
}
return true;