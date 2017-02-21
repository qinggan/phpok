<?php
/**
 * 取得账户余额
 * @package phpok\gateway\sms\gxt106com
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$rs['ext'] || !$rs['ext']['password'] || !$rs['ext']['account']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://www.gxt106.com/sms.aspx";
$data = array(
	'action'=>'overage',
	'userid'=>$rs['ext']['companyid'],
	'account'=>$rs['ext']['account'],
	'password'=>$rs['ext']['password']
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
//$info = $this->lib('html')->get_content($url);
$this->lib('html')->set_post(true);
$info = $this->lib('html')->get_content($url,http_build_query($data));
if(!$info || strpos($info,'<returnsms>') === false){
	return false;
}
$info = $this->lib('xml')->read($info,false);
if(!$info){
	return false;
}
$returnstatus = $info['returnstatus'] ? strtolower($info['returnstatus']) : 'fail';
if($returnstatus == 'sucess'){
	$count = intval($info['overage']);
	if($count<1){
		return '请充值，当前账户没有剩余短信数量';
	}
	if($count<30){
		return '您当前可用短信数量仅余<span style="color:red">'.$count.'</span>条，请及时充值';
	}
	return '您当前还可以使用<span style="color:red">'.$count.'</span>条短信';
}
if($this->config['debug']){
	phpok_log(print_r($info,true));
}
return false;