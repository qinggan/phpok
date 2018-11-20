<?php
/*****************************************************************************************
	文件： express/showapi/index.php
	备注： 易源接口之快递查询
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月19日 18时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//date_default_timezone_set("PRC");
if(!$express || !$rs || !$rs['code']){
	return false;
}
$ext = ($express['ext'] && is_string($express['ext'])) ? unserialize($express['ext']) : array();
$showapi_appid = trim($ext['app_id']);  //替换此值
$showapi_sign = trim($ext['app_secret']);  //替换此值。
$showapi_com = trim($ext['app_com']); 
$showapi_timestamp = date('YmdHis',$this->time);
$paramArr = array('showapi_appid'=>$showapi_appid,'showapi_timestamp' =>$showapi_timestamp,'com'=>$showapi_com,'nu'=>$rs['code']);
function showapi_createSign ($paramArr,$showapi_sign='') {
     $sign = "";
     ksort($paramArr);
     foreach ($paramArr as $key => $val) {
         if ($key != '' && $val != '') {
             $sign .= $key.$val;
         }
     }
     $sign.=$showapi_sign;
     $sign = strtoupper(md5($sign));
     return $sign;
}
function showapi_createStrParam ($paramArr) {
     $strParam = '';
     foreach ($paramArr as $key => $val) {
     if ($key != '' && $val != '') {
             $strParam .= $key.'='.urlencode($val).'&';
         }
     }
     return $strParam;
}

$sign = showapi_createSign($paramArr,$showapi_sign);
$strParam = showapi_createStrParam($paramArr);
$strParam .= 'showapi_sign='.$sign;
$url = 'http://route.showapi.com/64-19?'.$strParam;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$output = curl_exec($ch);
if(!$output){
	return array('content'=>P_Lang('远程获取数据失败'));
}
$curl_info = curl_getinfo($ch);
if($curl_info['http_code'] != '200'){
	return array('content'=>P_Lang('远程获取数据失败'));
}
curl_close($ch);
$tmplist = $this->lib('json')->decode($output);
if(!$tmplist){
	return array('content'=>P_Lang('检索异常'));
}
if($tmplist['showapi_res_code']){
	return array('content'=>$tmplist['showapi_res_error']);
}
$array = array('is_end'=>false);
//-1 待查询 0 查询异常 1 暂无记录 2 在途中 3 派送中 4 已签收 5 用户拒签 6 疑难件 7 无效单 8 超时单 9 签收失败 10 退回
$tmp = array(4,5,6,7,8,9,10);
if($tmplist['showapi_res_body'] && $tmplist['showapi_res_body']['status'] && in_array($tmplist['showapi_res_body']['status'],$tmp)){
	$array['is_end'] = true;
}
if(!$tmplist['showapi_res_body']['status']){
	return array('content'=>P_Lang('查询结果异常'));
}
if($tmplist['showapi_res_body'] && $tmplist['showapi_res_body']['data']){
	$array['content'] = array();
	foreach($tmplist['showapi_res_body']['data'] as $key=>$value){
		$tmp = array('time'=>$value['time'],'content'=>$value['context']);
		$array['content'][] = $tmp;
	}
}
$array['status'] = true;
return $array;