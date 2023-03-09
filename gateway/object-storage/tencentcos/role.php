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
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey'] || !$extinfo['bucket']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
if($this->session->val('TencentStsInfo')){
	$sts = $this->session->val('TencentStsInfo');
	if($sts['dateline'] && ($sts['dateline']+1200) > $this->time){
		$r['info'] = $sts;
		$r['status'] = true;
		return $r;
	}
}
$this->lib('tencentcos')->region($extinfo['region']);
$this->lib('tencentcos')->secret_id($extinfo['SecretId']);
$this->lib('tencentcos')->secret_key($extinfo['SecretKey']);
$this->lib('tencentcos')->bucket($extinfo['bucket']);
$info = $this->lib('tencentcos')->getTempKeys();
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
$info['dateline'] = $this->time;
$this->session->assign('TencentStsInfo',$info);
$r['info'] = $info;
$r['status'] = true;
return $r;