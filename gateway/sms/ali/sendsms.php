<?php
/**
 * 阿里发短信接口
 * @package phpok\gateway\sms\ali
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年01月21日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
function ali_gateway_create_sign($xlist,$blist,$appsecret='')
{
	$string = "GET\n";
	$string.= "*/*\n\n";
	$string.= 'application/text; charset=UTF-8'."\n\n";
	ksort($xlist);
	foreach($xlist as $key=>$value){
		$string.= $key.":".$value."\n";
	}
	$string.= "/singleSendSms?";
	ksort($blist);
	$query = '';
	foreach($blist as $key=>$value){
		$query.= $key.'='.$value."&";
	}
	$query = substr($query,0,-1);
	$string.= $query;
	phpok_log($string);
	return base64_encode(hash_hmac('sha256', $string, $appsecret, true));
}

$update = $this->get('update');
if($update){
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('未指定手机号');
	}
	if(!$this->lib('common')->tel_check($mobile,'mobile')){
		$this->error('手机号格式不正式');
	}
	$type = $this->get('type');
	if(!$type){
		$type = 'chkcode';
	}
	$content = $this->get('content');
	if(!$content){
		$tip = $type == 'chkcode' ? '未指定验证码内容' : '未指定订单编号';
		$this->error($tip);
	}
	$tplcode = $type == 'chkcode' ? $rs['ext']['tplvcode'] : $rs['ext']['tplorder'];
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://sms.market.alicloudapi.com/singleSendSms";
	$data = array(
		'ParamString'=>'{"'.$type.'":"'.$content.'"}',
		'RecNum'=>$mobile,
		'TemplateCode'=>$tplcode,
		'SignName'=>$rs['ext']['signame']
	);
	$url .= "?";
	foreach($data as $key=>$value){
		$url .= $key.'='.rawurlencode($value).'&';
	}
	if($rs['ext']['sendtype'] == 'appcode'){
		$this->lib('html')->set_header('Authorization','APPCODE '.$rs['ext']['appcode']);
	}else{
		$xlist = array('X-Ca-Key'=>$rs['ext']['appkey']);
		$xlist['X-Ca-Nonce'] = md5($this->time);
		$xlist['X-Ca-Stage'] = 'RELEASE';
		$sign = ali_gateway_create_sign($xlist,$data,$rs['ext']['appsecret']);
		$xlist['X-Ca-Signature'] = $sign;
		$xlist['X-Ca-Signature-Headers'] = "X-Ca-Key,X-Ca-Nonce,X-Ca-Stage";
		foreach($xlist as $key=>$value){
			$this->lib('html')->set_header($key,$value);
		}
		$this->lib('html')->set_header('Accept','*/*');
		$this->lib('html')->set_header('Content-Type','application/text; charset=UTF-8');
	}
	$info = $this->lib('html')->get_content($url);
	if(!$info){
		$this->error('短信发送失败');
	}
	$info = $this->lib('json')->decode($info);
	if(!$info['success']){
		$this->error($info['message']);
	}
	$this->success('短信发送成功');
	return true;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/ali/alisms.html','abs-file');