<?php
/**
 * 获取对象上传存储授权
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年2月27日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$r = array('status'=>false);
if(!$extinfo['regoin_id'] || !$extinfo['appkey'] || !$extinfo['appsecret'] || !$extinfo['bucket'] || !$extinfo['role']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
if($this->session->val('AliyunStsInfo')){
	$sts = $this->session->val('AliyunStsInfo');
	if($sts['dateline'] && ($sts['dateline']+3000) > $this->time){
		$r['info'] = $sts;
		$r['status'] = true;
		return $r;
	}
}
$regoin_id = $extinfo['regoin_id'];
//兼容STS的 region-id 写法
$regoin_id = str_replace("oss-","",$regoin_id);
//
$this->lib('aliyun')->regoin_id($regoin_id);
$this->lib('aliyun')->access_key($extinfo['appkey']);
$this->lib('aliyun')->access_secret($extinfo['appsecret']);
$this->lib('aliyun')->end_point($extinfo['end_point']);
$info = $this->lib('aliyun')->client();
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
if(is_array($info) && !$info['status']){
	$r['info'] = $info['error'];
	return $r;
}
$time = 3600;
$info = $this->lib('aliyun')->sts($extinfo['role'],'role',$time);
if(!$info['status']){
	$r['info'] = $info['error'];
	return $r;
}
$info['info']['dateline'] = $this->time;
$this->session->assign('AliyunStsInfo',$info['info']);
$r['info'] = $info['info'];
$r['status'] = true;
return $r;