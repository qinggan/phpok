<?php
/**
 * 维美发短信接口
 * @package phpok\gateway\sms\chinaweimei
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$update = $this->get('update');
if($update){
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('未指定手机号');
	}
	if(!$this->lib('common')->tel_check($mobile,'mobile')){
		$this->error('手机号格式不正式');
	}
	$content = $this->get('content');
	if(!$content){
		$this->error('未指定要发送的内容');
	}
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.smsbao.com/sms";
	$data = array(
		'u'=>$rs['ext']['account'],
		'p'=>md5($rs['ext']['password']),
		'm'=>$mobile,
		'c'=>$content
	);
	$url .= "?";
	foreach($data as $key=>$value){
		$url .= $key.'='.rawurlencode($value).'&';
	}
	$info = $this->lib('html')->get_content($url);
	if($info == '0'){
		$this->success('短信发送成功');
		return true;
	}
	$tip = '发送错误';
	if($info == '30'){
		$tip = '错误密码';
	}
	if($info == '40'){
		$tip = '账号不存在';
	}
	if($info == '41'){
		$tip = '余额不足';
	}
	if($info == '43'){
		$tip = 'IP地址限制';
	}
	if($info == '50'){
		$tip = '内容含有敏感词';
	}
	if($info == '51'){
		$tip = '手机号码不正确';
	}
	$this->error($tip);
	return false;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendsms.html','abs-file');