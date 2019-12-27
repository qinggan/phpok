<?php
/**
 * 订单变更通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年12月6日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$param['id'] || !$param['status']){
	return false;
}
$order = $this->model('order')->get_one($param['id']);
if(!$order){
	return false;
}
//订单信息
$this->assign('order',$order);
//订单产品信息
$rslist = $this->model('order')->product_list($order['id']);
$this->assign('rslist',$rslist);

$sql = "SELECT tid FROM ".$this->db->prefix."order_product WHERE order_id='".$order['id']."' AND tid>0 LIMIT 1";
$tmpchk = $this->db->get_one($sql);
if($tmpchk && $tmpchk['tid']){
	$sql = "SELECT site_id FROM ".$this->db->prefix."list WHERE id='".$tmpchk['tid']."'";
	$tmpchk = $this->db->get_one($sql);
	if($tmpchk && $tmpchk['site_id']){
		$this->model('gateway')->site_id($tmpchk['site_id']);
	}
}

//订单状态信息
$status_list = $this->model('site')->order_status_all();
if(!$status_list || !$status_list[$param['status']]){
	return false;
}
$status = $status_list[$param['status']];
$this->assign('status',$status);

//订单物流信息
$express_list = $this->model('order')->express_info_all($order['id']);
$this->assign('express_list',$express_list);

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
	if($tpl){
		$address = $this->model('order')->address($order['id']);
		$user = $this->model('user')->get_one($order['user_id']);
		$this->assign('address',$address);
		$this->assign('user',$user);
		$this->gateway('type','sms');
		$this->gateway('param','default');
		$mobile = $address['mobile'] ? $address['mobile'] : $user['mobile'];
		if($mobile){
			$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
			if($content){
				$content = strip_tags($content);
			}
			$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
			$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
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
	if($tpl){
		$this->gateway('type','sms');
		$this->gateway('param','default');
		$mobile = $this->gateway['param']['ext']['mobile'];
		if($mobile){
			$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
			if($content){
				$content = strip_tags($content);
			}
			$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
			$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
		}
	}
}

return true;