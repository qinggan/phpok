<?php
/*****************************************************************************************
	文件： task/post.php
	备注： 提交表单时启动的通知功能
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年12月20日 12时24分
*****************************************************************************************/
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
	$condition = "email!='' AND if_system=1";
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
?>