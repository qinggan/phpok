<?php
/**
 * 取得账户余额
 * @package phpok\gateway\sms\chinaweimei
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
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://cs.chinaweimei.com/wmsms/VerifySms.aspx";
$data = array(
	'action'=>'balance',
	'user'=>$rs['ext']['account'],
	'pass'=>$rs['ext']['password']
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
if(!$info || strpos($info,'{') === false){
	return false;
}

$info = $this->lib('json')->decode($info);
if($info['result_code'] == 'ok'){
	$count = intval($info['balance']);
	if($count<1){
		return '请充值，当前账户没有剩余短信数量';
	}
	if($count<30){
		return '您当前可用短信数量仅余<span style="color:red">'.$count.'</span>条，请及时充值';
	}
	return '您当前还可以使用<span style="color:red">'.$count.'</span>条短信';
	//return "您当前账户余额是：<span style='color:red'>".$info['balance'].'</span> 元';
}
if($this->config['debug']){
	phpok_log(print_r($info,true));
}
return false;