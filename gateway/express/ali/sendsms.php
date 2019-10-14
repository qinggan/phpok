<?php
/**
 * 阿里云市场发短信接口
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
	return base64_encode(hash_hmac('sha256', $string, $appsecret, true));
}

$update = $this->get('update');
if($update == 2){
	$tplcode = $this->get('tplcode','int');
	if(!$tplcode){
		$this->error('未指定模板标识');
	}
	$code = $this->model('email')->get_one($tplcode);
	if(!$code){
		$this->error('模板标签不存在');
	}
	$content = $code['content'];
	if(!$content){
		$this->success('变量:内容');
	}
	$content = strip_tags($content);
	$content = str_replace("\r\n","\n",$content);
	$tmp = explode("\n",$content);
	$content = '';
	foreach($tmp as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$value = trim($value);
		$tmp2 = explode(":",$value);
		if(!$tmp2[0] || !$tmp2[1]){
			continue;
		}
		if($content){
			$content .= "\n";
		}
		$content .= $tmp2[0].":";
	}
	$this->success($content);
}
if($update == 1){
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('未指定手机号');
	}
	if(!$this->lib('common')->tel_check($mobile,'mobile')){
		$this->error('手机号格式不正式');
	}
	$tplcode = $this->get('tplcode','int');
	if(!$tplcode){
		$this->error('未指定模板标签');
	}
	$content = $this->get('content');
	if(!$content){
		$this->error('未设置动态参数变量');
	}
	$code = $this->model('email')->get_one($tplcode);
	if(!$code){
		$this->error('模板标签不存在');
	}
	$tmp = explode("\n",$content);
	$codelist = array();
	foreach($tmp as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$value = trim($value);
		$t = explode(":",$value);
		if($t[0] && $t[1]){
			$codelist[$t[0]] = $t[1];
		}
	}
	$paramString = $codelist ? $this->lib('json')->encode($codelist) : '{}';
	$tplcode = $code['title'];
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://sms.market.alicloudapi.com/singleSendSms";
	$data = array(
		'ParamString'=>$paramString,
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
//读取短信模板
$smslist = $this->model('email')->get_list("identifier LIKE 'sms_%'",0,999);
$this->assign('smslist',$smslist);
$this->view($this->dir_root.'gateway/'.$rs['type'].'/ali/alisms.html','abs-file');