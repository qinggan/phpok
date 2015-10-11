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
if(!$rs['ext'] || !$rs['ext']['password'] || !$rs['ext']['account'] || !$extinfo['mobile'] || !$extinfo['content']){
	return false;
}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.duanxin.cm/";
$data = array(
	'action'=>'send',
	'username'=>$rs['ext']['account'],
	'password'=>strtolower(md5($rs['ext']['password'])),
	'phone'=>$extinfo['mobile'],
	'content'=>$extinfo['content'],
	'encode'=>'utf8'
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
return $info;
?>