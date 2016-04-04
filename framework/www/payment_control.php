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
			//$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$url = $this->url('order','info','sn='.$rs['sn']);
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
			//$order = $this->model('order')->get_one_from_sn($rs['sn']);
			//if(!$order){
			//	error(P_Lang('订单信息不存在'),'','error');
			//}
			$url = $this->url('order','info','sn='.$rs['sn']);
			$this->_location($url);
		}
		$this->view('payment_show');
	}
}

?>