<?php
/***********************************************************
	Filename: {phpok}/www/payment_control.php
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

	//创建一条支付接口
	public function create_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			$sn = $this->_create_sn();
		}
		$type = $this->get('type');
		if(!$type){
			$type = 'order';
		}
		$price = $this->get('price','float');
		if(!$price){
			$this->json(P_Lang('未指定金额'));
		}
		$currency_id = $this->get('currency_id');
		if(!$currency_id){
			$currency_id = $this->site['currency_id'];
		}
		if($type == 'order'){
			$title = P_Lang('订单：{sn}',array('sn'=>$sn));
		}elseif($type == 'recharge'){
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
		//检测sn是否已存在
		$chk = $this->model('payment')->log_check($sn);
		if($chk){
			if($chk['status']){
				$this->json(P_Lang('订单{sn}已支付完成，不能重复执行'));
			}
			$array = array('sn'=>$sn,'type'=>$type,'payment_id'=>$payment,'title'=>$title,'content'=>$title);
			$array['dateline'] = $this->time;
			$array['price'] = $price;
			$array['currency_id'] = $currency_id;
			$this->model('payment')->log_update($array,$chk['id']);
			$this->json($chk['id'],true);
		}
		$array = array('sn'=>$sn,'type'=>$type,'payment_id'=>$payment,'title'=>$title,'content'=>$title);
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->user['id'];
		$array['price'] = $price;
		$array['currency_id'] = $currency_id;
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->json(P_Lang('支付记录创建失败'));
		}
		//更新订单状态
		if($type == 'order'){
			$order = $this->model('order')->get_one_from_sn($sn);
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
			$array['price'] = $price;
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

	//提交支付
	public function submit_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定支付订单ID'),'','error');
		}
		$log = $this->model('payment')->log_one($id);
		if(!$log){
			error(P_Lang('订单信息不存在'),'','error');
		}
		if($log['status']){
			error(P_Lang('订单已支付过了，不能再次执行'),'','error');
		}
		$payment_rs = $this->model('payment')->get_one($log['payment_id']);
		if(!$payment_rs){
			error(P_Lang('支付方式不存在'),'','error');
		}
		if(!$payment_rs['status']){
			error(P_Lang('支付方式未启用'),'','error');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/submit.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)),'','error');
		}
		include($file);
		$name = $payment_rs['code']."_submit";
		$payment = new $name($log,$payment_rs);
		$payment->submit();
	}

	public function notice_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('执行异常，请检查，缺少参数ID'),'','error');
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			error(P_Lang('订单信息不存在'),$this->url('index'),'error');
		}
		if($rs['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$url = $this->url('order','info','id='.$order['id']);
		}elseif($rs['type'] == 'recharge'){
			$url = $this->url('usercp','wealth','sn='.$rs['sn']);
		}else{
			$url = $this->url('payment','show','id='.$id);
		}
		//同步通知
		if($rs['status']){
			error(P_Lang('您的订单付款成功，请稍候…'),$url,'ok');
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notice.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)),'','error');
		}
		include($file);
		$name = $payment_rs['code'].'_notice';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		error(P_Lang('您的订单付款成功，请稍候…'),$url,'ok');
	}

	//异步通知方案
	//考虑到异步通知存在读不到$_SESSION问题，使用sn和pass组合
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('error');
		}
		$rs = $this->model('payment')->log_check($sn);
		if(!$rs){
			exit('error');
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		if(!$payment_rs){
			exit('error');
		}
		if(!$payment_rs['status']){
			exit('error');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('error');
		}
		include($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		exit('success');
	}

	public function show_f()
	{
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未指定ID'),'','error');
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			error(P_Lang('数据不存在，请检查'),'','error');
		}
		if($rs['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($rs['sn']);
			if(!$order){
				error(P_Lang('订单信息不存在'),'','error');
			}
			$url = $this->url('order','info','id='.$order['id']);
			$this->_location($url);
		}
		$this->view('payment_show');
	}
}

?>