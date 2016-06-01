<?php
/*****************************************************************************************
	文件： task/order.php
	备注： 订单通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月29日 23时17分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$param['id'] || !$param['status']){
	return false;
}
$order = $this->model('order')->get_one($param['id']);
if(!$order){
	return false;
}
$this->assign('order',$order);

$status_list = $this->model('site')->order_status_all();
if(!$status_list || !$status_list[$param['status']]){
	return false;
}
$status = $status_list[$param['status']];
$this->assign('status',$status);

//通知会员
if($status['email_tpl_user']){
	$tpl = $this->model('email')->tpl($status['email_tpl_user']);
	if($tpl){
		$address = $this->model('order')->address($order['id']);
		$user = $this->model('user')->get_one($order['user_id']);
		$this->assign('address',$address);
		$this->assign('user',$user);
		$this->gateway('type','email');
		$this->gateway('param','default');
		$email = $order['email'] ? $order['email'] : ($address['email'] ? $address['email'] : $user['email']);
		if(!$email){
			return false;
		}
		$fullname = $address['fullname'] ? $address['fullname'] : ($user['fullname'] ? $user['fullname'] : $user['user']);
		$this->assign('fullname',$fullname);
		$title = $this->fetch($tpl['title'],'msg');
		$content = $this->fetch($tpl['content'],'msg');
		if($title && $email && $content){
			$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
			$this->gateway('exec',$array);
		}
	}
}

if($status['sms_tpl_user']){
	$tpl = $this->model('email')->tpl($status['sms_tpl_user']);
	if($tpl && $tpl['content']){
		$address = $this->model('order')->address($order['id']);
		$user = $this->model('user')->get_one($order['user_id']);
		$this->assign('address',$address);
		$this->assign('user',$user);
		$this->gateway('type','sms');
		$this->gateway('param','default');
		$mobile = $address['mobile'] ? $address['mobile'] : $user['mobile'];
		if($mobile){
			$content = $this->fetch($tpl['content'],'msg');
			if($content){
				$content = strip_tags($content);
			}
			if($content){
				$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content));
			}
		}
	}
}

if($status['email_tpl_admin']){
	$tpl = $this->model('email')->tpl($status['email_tpl_admin']);
	if($tpl){
		$condition = "email!='' AND if_system=1";
		$admin = $this->model('admin')->get_list($condition,0,1);
		if($admin){
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
}
//短信通知管理员
if($status['sms_tpl_admin']){
	$tpl = $this->model('email')->tpl($status['sms_tpl_admin']);
	if($tpl && $tpl['content']){
		$this->gateway('type','sms');
		$this->gateway('param','default');
		$mobile = $this->gateway['param']['ext']['mobile'];
		if($mobile){
			$content = $this->fetch($tpl['content'],'msg');
			if($content){
				$content = strip_tags($content);
			}
			if($content){
				$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content));
			}
		}
	}
}

return true;


?>