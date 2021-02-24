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
if(!$extinfo['content'] || !$extinfo['mobile']){
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
$data = array('code'=>$extinfo['content'],'mobile'=>$extinfo['mobile']);
$t = $this->lib('phpok')->content($data);
if(!$t){
	$this->error('发送失败');
}
if($t && !$t['status']){
	if($this->config['debug']){
		phpok_log($t['info']);
	}
	$this->error($t['info']);
	return false;
}
return true;