<?php
/**
 * 邮件营销（短信通知）
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年7月23日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$condition = "t.sms_id>0";
$tasklist = $this->model('edm')->task_list($condition,0,999);
if(!$tasklist){
	return true;
}

foreach($tasklist as $key=>$value){
	$queue = $this->model('edm')->queue_sms($value['id']);
	if(!$queue){
		continue;
	}
	if(!$queue['sms_gateway']){
		continue;
	}
	$email_rs = $this->model('edm')->get_one($queue['email_id']);
	if(!$email_rs){
		$this->model('edm')->queue_delete($queue['id']);
		continue;
	}
	if(!$email_rs['email']){
		$this->model('edm')->email_delete($email_rs['id']);
		$this->model('edm')->queue_delete($queue['id']);
		continue;
	}
	if(!$email_rs['mobile']){
		continue;
	}
	//发送邮件
	$tplinfo = $this->model('email')->get_one($value['sms_id']);
	if(!$tplinfo){
		continue;
	}
	//开始发送邮件
	$this->gateway('type','sms');
	$this->gateway('param',$queue['sms_gateway']);
	$this->assign('username',$email_rs['username']);
	$this->assign('rs',$email_rs);
	$params = $this->model('edm')->params();
	if(!$params['hometxt'] && $params['homelink']){
		$params['hometxt'] = parse_url($params['homelink'],PHP_URL_HOST);
	}
	$this->assign('params',$params);
	$title = $this->fetch($tplinfo['title'],'msg');
	$content = $this->fetch($tplinfo['content'],'msg');
	if(!$title || !$content){
		continue;
	}
	$content = strip_tags($content);
	$this->gateway('exec',array('mobile'=>$email_rs['mobile'],'content'=>$content,'title'=>$title,'identifier'=>$tplinfo['identifier']));
	$data = array('sms_status'=>1,'sms_sendtime'=>$this->time);
	$this->model('edm')->queue_update($data,$queue['id']);
}
return true;