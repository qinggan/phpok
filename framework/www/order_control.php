<?php
/***********************************************************
	Filename: {phpok}/www/order_control.php
	Note	: 订单信息管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	//取得订单列表
	function index_f()
	{
		$backurl = $this->url('order');
		if(!$_SESSION['user_id']) error('您还不是会员，请先登录',$this->url('login','','_back='.rawurlencode($backurl)));
		//读取会员下的订单列表
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($pageid,'int');
		if(!$pageid) $pageid = 1;
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$_SESSION['user_id']."'";
		$this->assign('pageid',$pageid);
		$pageurl = $this->url('order');
		$this->assign('pageurl',$pageurl);
		$total = $this->model('order')->get_count($condition);
		$this->assign('total',$total);
		$this->assign('psize',$psize);
		if($total)
		{
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
		}
		$this->view('order_list');
	}

	//查看订单信息
	function info_f()
	{
		$rs = $this->auth_check();
		//订单状态
		$status_list = $this->model('order')->status_list();
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$rs['pay_status_info'] = ($status_list && $status_list[$rs['pay_status']]) ? $status_list[$rs['pay_status']] : $rs['pay_status'];
		$this->assign('rs',$rs);
		//取得订单的地址
		$address = $this->model('order')->address_list($rs['id']);
		$this->assign('shipping',$address['shipping']);
		$this->assign('billing',$address['billing']);
		//订单下的产品列表
		$rslist = $this->model('order')->product_list($rs['id']);
		if($rslist)
		{
			$thumb = '';
			foreach($rslist AS $key=>$value)
			{
				if($value['thumb']) $thumb[] = $value['thumb'];
			}
			if($thumb && count($thumb)>0)
			{
				$tlist = $this->model('data')->res_info($thumb,true);
				if($tlist)
				{
					foreach($rslist AS $key=>$value)
					{
						if($value['thumb']) $value['thumb'] = $tlist[$value['thumb']];
						$rslist[$key] = $value;
					}
				}
			}
		}
		$this->assign('rslist',$rslist);

		//取得付款方式
		if(!$rs['pay_date'])
		{
			$paylist = $this->model('payment')->get_all($this->site['id'],1);
			$this->assign("paylist",$paylist);
		}
		else
		{
			$payment = $this->model('payment')->get_one($rs['pay_id']);
			$this->assign('payment',$payment);
		}

		$this->view('order_info');
	}

	//提交支付
	function payment_f()
	{
		$rs = $this->auth_check();
		$this->assign('rs',$rs);
		$rslist = $this->model('payment')->get_all($this->site['id']);
		$this->assign('rslist',$rslist);
		$this->view("order_payment");
	}
	
	function auth_check()
	{
		$sn = $this->get('sn');
		$back = $this->get('back');
		if(!$back) $back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url;
		//判断订单是否存在
		if($sn) $rs = $this->model('order')->get_one_from_sn($sn);
		if(!$rs)
		{
			$id = $this->get('id','int');
			if(!$id) error("无法获取订单信息，请检查！",$back,'error');
			$rs = $this->model('order')->get_one($id);
			if(!$rs) error("订单信息不存在，请检查！",$back,'error');
		}
		$passwd = $this->get('passwd');
		if(!$passwd)
		{
			if(!$_SESSION['user_id'] || $_SESSION['user_id'] != $rs['user_id']) error('您没有权限查看此订单',$back,'error');
		}
		else
		{
			if($passwd != $rs['passwd']) error('您没有权限维护订单：'.$rs['sn'],$back,'error');
		}
		return $rs;
	}
}

?>