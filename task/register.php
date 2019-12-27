<?php
/**
 * 注册通知信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$param['id'] || !$param['act']){
	return false;
}
$user = $this->model('user')->get_one($param['id'],'id',false,false);
if(!$user){
	return false;
}
$this->assign('user',$user);
if($param['act'] == 'active'){
	if($user['status']){
		return false;
	}
	if($user['email']){
		$emailtpl = 'email_register_active';
		$tpl = $this->model('email')->tpl($emailtpl);
		if(!$tpl){
			return false;
		}
		$this->gateway('type','email');
		$this->gateway('param','default');
		$title = $this->fetch($tpl['title'],'msg');
		$content = $this->fetch($tpl['content'],'msg');
		if($title && $content){
			$array = array('email'=>$user['email'],'fullname'=>$user['user'],'title'=>$title,'content'=>$content);
			$this->gateway('exec',$array);
		}
	}
	if($user['mobile']){
		$mobiletpl = 'sms_register_active';
		$tpl = $this->model('email')->tpl($mobiletpl);
		if(!$tpl){
			return false;
		}
		$this->gateway('type','sms');
		$this->gateway('param','default');
		$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
		if($content){
			$content = strip_tags($content);
		}
		$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
		$this->gateway('exec',array('mobile'=>$user['mobile'],'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
	}
}
return true;