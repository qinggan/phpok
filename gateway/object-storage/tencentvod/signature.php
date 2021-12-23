<?php
/**
 * 生成签名
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月26日
**/

$r = array('status'=>false);
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
// 确定签名的当前时间和失效时间
$expired = $this->time + 86400;  // 签名有效期：1天
// 向参数列表填入参数
$arg_list = array(
   "secretId" => $extinfo['SecretId'],
   "currentTimeStamp" => $this->time,
   "expireTime" => $expired,
   "random" => rand(),
   "storageRegion"=>$extinfo['region']);
// 计算签名
$original = http_build_query($arg_list);
$signature = base64_encode(hash_hmac('SHA1', $original, $extinfo['SecretKey'], true).$original);

$r['info'] = $signature;
$r['status'] = true;
return $r;