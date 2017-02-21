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
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.duanxin.cm/";
	$data = array(
		'action'=>'send',
		'username'=>$rs['ext']['account'],
		'password'=>md5($rs['ext']['password']),
		'phone'=>$mobile,
		'content'=>$content,
		'encode'=>'utf8'
	);
	$url .= "?";
	foreach($data as $key=>$value){
		$url .= $key.'='.rawurlencode($value).'&';
	}
	$info = $this->lib('html')->get_content($url);
	if($info == '100'){
		$this->success('短信发送成功');
		return true;
	}
	$tip = '发送错误';
	if($info == '101'){
		$tip = '验证失败';
	}
	if($info == '102'){
		$tip = '短信不足';
	}
	if($info == '103'){
		$tip = '操作失败';
	}
	if($info == '104'){
		$tip = '非法字符';
	}
	if($info == '105'){
		$tip = '内容过多';
	}
	if($info == '106'){
		$tip = '号码过多';
	}
	if($info == '107'){
		$tip = '频率过快';
	}
	if($info == '108'){
		$tip = '号码内容空';
	}
	if($info == '109'){
		$tip = '账号冻结';
	}
	if($info == '110'){
		$tip = '禁止频繁单条发送';
	}
	if($info == '111'){
		$tip = '系统暂定发送';
	}
	if($info == '112'){
		$tip = '号码错误';
	}
	if($info == '113'){
		$tip = '定时时间格式不对';
	}
	if($info == '114'){
		$tip = '账号被锁，10分钟后登录';
	}
	if($info == '115'){
		$tip = '连接失败';
	}
	if($info == '116'){
		$tip = '禁止接口发送';
	}
	if($info == '117'){
		$tip = '绑定IP不正确';
	}
	if($info == '120'){
		$tip = '系统升级';
	}
	$this->error($tip);
	return false;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendsms.html','abs-file');