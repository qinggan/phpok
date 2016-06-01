<?php
/***********************************************************
	Filename: {phpok}/api/payment_control.php
	Note	: 付款操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月14日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function create_f()
	{
		$token = $this->get('token');
		if(!$token){
			$this->json(P_Lang('数据传参不完整，请检查'));
		}
		if(!$this->site){
			$this->json(P_Lang('数据异常，无法获取站点信息'));
		}
		$info = $this->lib('token')->decode($token);
		if(!$info || !$info['price']){
			$this->json(P_Lang('数据不完整，请检查'));
		}
		if(!$info['sn']){
			$info['sn'] = $this->_create_sn();
		}
		if(!$info['type']){
			$info['type'] = 'order';
		}
		if(!$info['currency_id']){
			$info['currency_id'] = $this->site['currency_id'];
		}
		if($info['type'] == 'order'){
			$title = P_Lang('订单：{sn}',array('sn'=>$sn));
		}elseif($info['type'] == 'recharge'){
			$title = P_Lang('充值：{sn}',array('sn'=>$sn));
		}else{
			$title = $this->get('title');
			if(!$title){
				$title = P_Lang('其他：{sn}',array('sn'=>$sn));
			}
		}
		$payment = $this->get('payment','int');
		if(!$payment){
			$this->json(P_Lang('未指定付款方式'));
		}
		$payment_rs = $this->model('payment')->get_one($payment);
		if(!$payment_rs){
			$this->json(P_Lang('支付方式不存在'));
		}
		if(!$payment_rs['status']){
			$this->json(P_Lang('支付方式未启用'));
		}
		$chk = $this->model('payment')->log_check($info['sn']);
		if($chk){
			if($chk['status']){
				$this->json(P_Lang('订单{sn}已支付完成，不能重复执行',array('sn'=>$info['sn'])));
			}
			$array = array('type'=>$info['type'],'payment_id'=>$payment,'title'=>$title,'content'=>$title);
			$array['dateline'] = $this->time;
			$array['price'] = $info['price'];
			$array['currency_id'] = $info['currency_id'];
			$this->model('payment')->log_update($array,$chk['id']);
			$this->json($chk['id'],true);
		}
		$array = array('sn'=>$info['sn'],'type'=>$info['type'],'payment_id'=>$payment,'title'=>$title,'content'=>$title);
		$array['dateline'] = $this->time;
		$array['user_id'] = $info['user_id'] ? $info['user_id'] : $this->user['id'];
		$array['price'] = $info['price'];
		$array['currency_id'] = $info['currency_id'];
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->json(P_Lang('支付记录创建失败'));
		}
		//更新订单状态
		if($info['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($info['sn']);
			if(!$order){
				$this->model('payment')->log_delete($insert_id);
				$this->json(P_Lang('订单信息不存在'));
			}
			//更新支付状态
			$this->model('order')->update_order_status($order['id'],'unpaid');
			//写入日志
			$note = P_Lang('订单进入等待支付状态，编号：{sn}',array('sn'=>$sn));
			$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$this->user['user'],'note'=>$note);
			$this->model('order')->log_save($log);
			//增加order_payment
			$array = array('order_id'=>$order['id'],'payment_id'=>$payment_rs['id']);
			$array['title'] = $payment_rs['title'];
			$array['price'] = $info['price'];
			$array['startdate'] = $this->time;
			$order_payment = $this->model('order')->order_payment($order['id']);
			if(!$order_payment){
				$this->model('order')->save_payment($array);
			}else{
				$this->model('order')->save_payment($array,$order_payment['id']);
			}
		}
		$this->json($insert_id,true);
	}

	private function _create_sn()
	{
		$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$rand_str = '';
		for($i=0;$i<3;$i++){
			$rand_str .= $a[rand(0,25)];
		}
		$rand_str .= rand(1000,9999);
		$rand_str .= date("YmdHis",$this->time);
		return $rand_str;
	}

	//异步通知
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('fail');
		}
		$rs = $this->model('order')->get_one_from_sn($sn);
		if(!$rs){
			exit('fail');
		}
		$payment_rs = $this->model('payment')->get_one($rs['pay_id']);
		if(!$payment_rs){
			exit('fail');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('fail');
		}
		include_once($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
	}


	public function status_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->json(P_Lang('支付信息不存在'));
		}
		if($rs['status']){
			$this->json(true);
		}else{
			$this->json(P_Lang('等待支付完成'));
		}
	}

	//查询订单接口
	public function query_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			$this->json(P_Lang('未指定订单编号'));
		}
		$rs = $this->model('payment')->log_check($sn);
		if(!$rs){
			$this->json(P_Lang('订单不存在'));
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		if(!$payment_rs){
			$this->json(P_Lang('支付方式不存在'));
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/query.php';
		if(!file_exists($file)){
			$this->json(P_Lang('查询接口不存在'));
		}
		include_once($file);
		$name = $payment_rs['code'].'_query';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
	}

	
	//权限验证
	private function auth_check()
	{
		$sn = $this->get('sn');
		$back = $this->get('back');
		if(!$back) $back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url;
		//判断订单是否存在
		if($sn) $rs = $this->model('order')->get_one_from_sn($sn,$_SESSION['user_id']);
		if(!$rs)
		{
			$id = $this->get('id','int');
			if(!$id) error("无法获取订单信息，请检查！",$back,'error');
			$rs = $this->model('order')->get_one($id);
			if(!$rs) error("订单信息不存在，请检查！",$back,'error');
		}
		//判断是否有维护订单权限
		if($_SESSION['user_id'])
		{
			if($rs['user_id'] != $_SESSION['user_id']) error('您没有权限维护此订单：'.$rs['sn'],$back,'error');
		}
		else
		{
			$passwd = $this->get('passwd');
			if($passwd != $rs['passwd']) error('您没有权限维护此订单：'.$rs['sn'],$back,'error');
		}
		return $rs;
	}

}

?>