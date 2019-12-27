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
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.smsbao.com/sms";
$data = array(
	'u'=>$rs['ext']['account'],
	'p'=>strtolower(md5($rs['ext']['password'])),
	'm'=>$extinfo['mobile'],
	'c'=>$extinfo['content']
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
if($info != '0'){
	if($this->config['debug']){
		phpok_log($info);
	}
	return false;
}
return true;