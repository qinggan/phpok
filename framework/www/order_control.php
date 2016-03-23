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
		if(!$_SESSION['user_id']){
			error(P_Lang('您还不是会员，请先登录'),$this->url('login','','_back='.rawurlencode($backurl)));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($pageid,'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$_SESSION['user_id']."'";
		$this->assign('pageid',$pageid);
		$pageurl = $this->url('order');
		$this->assign('pageurl',$pageurl);
		$total = $this->model('order')->get_count($condition);
		$this->assign('total',$total);
		$this->assign('psize',$psize);
		if($total){
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
		}
		$this->view('order_list');
	}

	//查看订单信息
	function info_f()
	{
		$rs = $this->auth_check();
		$status_list = $this->model('order')->status_list();
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$this->assign('rs',$rs);
		$address = $this->model('order')->address($rs['id']);
		$this->assign('address',$address);
		$rslist = $this->model('order')->product_list($rs['id']);
		$this->assign('rslist',$rslist);
		//获取发票信息
		$invoice = $this->model('order')->invoice($rs['id']);
		$this->assign('invoice',$invoice);
		//获取价格
		$price_tpl_list = $this->model('site')->price_status_all();
		$order_price = $this->model('order')->order_price($rs['id']);
		if($price_tpl_list && $order_price){
			$pricelist = array();
			foreach($price_tpl_list as $key=>$value){
				if(!$value['status']){
					continue;
				}
				$tmp = array();
				$tmp['price'] = price_format($order_price[$key],$rs['currency_id']);
				$tmp['title'] = $value['title'];
				$pricelist[$key] = $tmp;
			}
			$this->assign('pricelist',$pricelist);
		}
		//检查订单是否已支付过
		$paycheck = $this->model('order')->order_payment($rs['id']);
		if($paycheck && $paycheck['dateline']){
			$this->assign('pay_end',true);
			$this->assign('payinfo',$paycheck);
		}else{
			$paylist = $this->model('payment')->get_all($this->site['id'],1);
			$this->assign("paylist",$paylist);
			//创建支付链接
			$tmp = array('sn'=>$rs['sn'],'price'=>$rs['price'],'user_id'=>$_SESSION['user_id'],'type'=>'order');
			$tmp['currency_id'] = $rs['currency_id'];
			$token = $this->lib('token')->encode($tmp);
			$payment_url = $this->url('payment','create','token='.rawurlencode($token),'api');
			$this->assign('payment_url',$payment_url);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		$this->assign('loglist',$loglist);
		$this->view('order_info');
	}
	
	private function auth_check()
	{
		$sn = $this->get('sn');
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url;
		}
		//判断订单是否存在
		if($sn){
			$rs = $this->model('order')->get_one_from_sn($sn);
		}
		if(!$rs){
			$id = $this->get('id','int');
			if(!$id){
				error(P_Lang('无法获取订单信息'),$back,'error');
			}
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				error(P_Lang('订单信息不存在'),$back,'error');
			}
		}
		$passwd = $this->get('passwd');
		if(!$passwd){
			if(!$_SESSION['user_id'] || $_SESSION['user_id'] != $rs['user_id']){
				error(P_Lang('您没有权限查看此订单'),$back,'error');
			}
		}else{
			if($passwd != $rs['passwd']){
				error(P_Lang('您没有权限查看此订单'),$back,'error');
			}
		}
		return $rs;
	}
}

?>