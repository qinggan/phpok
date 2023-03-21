<?php
/**
 * 邮件发送
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年10月9日
**/


/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}


if(!$rs['ext'] || !$rs['ext']['server'] || !$rs['ext']['app_id'] || !$rs['ext']['app_key']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
if(!$extinfo['mobile']){
	if($this->config['debug']){
		phpok_log(print_r($extinfo,true));
	}
	return false;
}

$this->lib('phpok')->server_url($rs['ext']['server']);
if($rs['ext'] && $rs['ext']['ip']){
	$this->lib('phpok')->ip($rs['ext']['ip']);
}
$this->lib('phpok')->app_id($rs['ext']['app_id']);
$this->lib('phpok')->app_key($rs['ext']['app_key']);

$code = array();
if(is_numeric($extinfo['content'])){
	$code['code'] = $extinfo['content'];
}else{
	$tmplist = explode(",",$extinfo['content']);
	foreach($tmplist as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp = explode(":",$value);
		if($tmp[0] && $tmp[1] != ''){
			$code[$tmp[0]] = trim($tmp[1]);
		}
	}
}
$data = array('mobile'=>$extinfo['mobile']);
if($rs['ext']['signame']){
	$data['sign'] = $rs['ext']['signame'];
}
if($rs['ext']['tplcode']){
	$data['tpl_id'] = $rs['ext']['tplcode'];
}
if(is_numeric($extinfo['content'])){
	$data['code'] = $extinfo['content'];
}else{
	$data['code'] = $code;
	$data['tpl_id'] = $extinfo['title'];
}

$t = $this->lib('phpok')->content($data);
if(!$t){
	$this->error('发送失败');
}
if($t && !$t['status']){
	$info = $t['info'] ? $t['info'] : $t['error'];
	if($this->config['debug']){
		phpok_log($info);
	}
	$this->error($info);
	return false;
}
return true;