<?php
/**
 * 发布通知管理员
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$param['id'] || !$param['status']){
	return false;
}
$id = $param['id'];
$status = $param['status'];
$rs = $this->model('content')->get_one($id,false);
$project = $this->model('project')->get_one($rs['project_id'],false);
if(!$project){
	return false;
}
$this->assign('rs',$rs);
if($rs['user_id']){
	$user_rs = $this->model('user')->get_one($rs['user_id']);
	$this->assign('user_rs',$user_rs);
}
if($project['etpl_admin']){
	$this->gateway('type','email');
	$this->gateway('param','default');
	$tpl = $this->model('email')->tpl($project['etpl_admin']);
	$condition = "email!='' AND if_system=1 AND status=1";
	$admin = $this->model('admin')->get_list($condition,0,1);
	if($tpl && $admin){
		$admin = current($admin);
		$this->assign('admin',$admin);
		$email = $admin['email'];
		$fullname = $admin['fullname'] ? $admin['fullname'] : $admin['account'];
		$this->assign('fullname',$fullname);
		$title = $this->fetch($tpl['title'],'msg');
		$content = $this->fetch($tpl['content'],'msg');
		if($title && $email && $content){
			$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
			$this->gateway('exec',$array);
		}
	}
}
if($project['etpl_user'] && $rs['user_id'] && $user_rs && $user_rs['email']){
	$this->gateway('type','email');
	$this->gateway('param','default');
	$tpl = $this->model('email')->tpl($project['etpl_user']);
	if($tpl){
		$fullname = $usre_rs['fullname'] ? $usre_rs['fullname'] : $usre_rs['user'];
		$title = $this->fetch($tpl['title'],'msg');
		$content = $this->fetch($tpl['content'],'msg');
		if($title && $email && $content){
			$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
			$this->gateway('exec',$array);
		}
	}
}