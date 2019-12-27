<?php
/**
 * 容联云通信发短信接口
 * @package phpok\gateway\sms\chinaweimei
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年12月1日
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
	$tplcode = $this->get('tplcode','int');
	if(!$tplcode){
		$this->error('未指定模板标识');
	}
	$code = $this->model('email')->get_one($tplcode);
	if(!$code){
		$this->error('模板标签不存在');
	}
	$content = $this->get('content');
	if(!$content){
		$this->error('未指定要发送的内容');
	}
	//口令生成
	$time = date("YmdHis",$this->time);
	$sign = strtoupper(md5($rs['ext']['account'].$rs['ext']['password'].$time));
	//
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "https://app.cloopen.com:8883";
	$url.= '/'.$rs['ext']['softVersion'].'/Accounts/'.$rs['ext']['account'].'/SMS/TemplateSMS?sig='.$sign;
	//设置Header
	$jlist = array();
	$jlist['to'] = $mobile;
	$jlist['appId'] = $rs['ext']['appId'];
	$jlist['templateId'] = $code['title'];
	$jlist['datas'] = explode("\n",$content);
	$post_data = $this->lib('json')->encode($jlist);
	$this->lib('curl')->is_post(true);
	$this->lib('curl')->set_header('Accept','application/json');
	$this->lib('curl')->set_header('Content-Type','application/json;charset=utf-8');
	$this->lib('curl')->set_header('Authorization',base64_encode($rs['ext']['account'].':'.$time));
	$this->lib('curl')->post_data($post_data);
	$info = $this->lib('curl')->get_content($url);
	if(!$info){
		$this->error(P_Lang('短信发送失败'));
	}
	$info = $this->lib('json')->decode($info);
	if($info['statusCode'] == '000000'){
		$this->success('短信发送成功');
	}
	$errInfo = $this->lib('file')->cat($this->dir_gateway.'sms/'.$rs['code'].'/error.json');
	$errInfo = $this->lib('json')->decode($errInfo);
	if($errInfo[$info['statusCode']]){
		$this->error($errInfo[$info['statusCode']]);
	}
	$this->error('短信发送失败');
}
//读取短信模板
$smslist = $this->model('email')->get_list("identifier LIKE 'sms_%'",0,999);
$this->assign('smslist',$smslist);
$this->view($this->dir_gateway.'sms/'.$rs['code'].'/sendsms.html','abs-file');