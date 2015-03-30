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
		$chk = $this->auth_check();
		$rs = $chk['rs'];
		$error_url = $chk['error_url'];
		unset($chk);
		if($rs['pay_end']){
			error(P_Lang('该订单已结束，不能再执行付款操作'),$error_url,'error');
		}
		$payment = $this->get('payment','int');
		if(!$payment){
			error(P_Lang('未指定付款方式'),$error_url,"error");
		}
		$payment_rs = $this->model('payment')->get_one($payment);
		if(!$payment_rs){
			error(P_Lang('支付方式不存在'),$error_url,'error');
		}
		if(!$payment_rs['status']){
			error(P_Lang('支付方式未启用'),$error_url,'error');
		}
		//进入支付页
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/submit.php';
		if(!is_file($file)){
			error(P_Lang('支付接口异常，请检查'),$error_url,'error');
		}
		include_once($file);
		//更新定单支付信息
		$data = array('pay_id'=>$payment_rs['id'],'pay_title'=>$payment_rs['title']);
		$data['pay_date'] = $this->time;
		$data['pay_status'] = '正在支付';
		$currency = $payment_rs['currency']['id'] ? $payment_rs['currency']['id'] : $rs['currency_id'];
		$price = price_format_val($rs['price'],$rs['currency_id'],$currency);
		$data['pay_price'] = $price;
		$data['pay_currency'] = $currency;
		if($currency){
			$currency_rs = $this->model('currency')->get_one($currency);
			$currency_code = $currency_rs['code'];
			$pay_currency_rate = $currency_rs['val'];
		}else{
			$currency_code = 'CNY';
			$currency_rate = '1.00000000';
		}
		$data['pay_currency_code'] = $currency_code;
		$data['pay_currency_rate'] = $pay_currency_rate;
		$data['pay_end'] = 0;
		$this->model('order')->save($data,$rs['id']);
		$name = $payment_rs['code']."_submit";
		$payment = new $name($rs,$payment_rs);
		$payment->submit();
	}

	//同步通知处理方案
	public function notice_f()
	{
		$chk = $this->auth_check();
		$rs = $chk['rs'];
		$url = $chk['error_url'];
		unset($chk);
		if($rs['pay_end']){
			error(P_Lang('您的订单付款成功，请稍候…'),$url,'ok');
		}
		if(!$rs['pay_id']){
			error(P_Lang('未指定支付方式'),$url,'ok');
		}
		$payment_rs = $this->model('payment')->get_one($rs['pay_id']);
		if(!$payment_rs){
			error(P_Lang('付款方式不存在'),$url,'error');
		}
		if(!$payment_rs['status']){
			error(P_Lang('付款方式未启用'),$url,'error');
		}
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/notice.php';
		if(!is_file($file)){
			error(P_Lang('支付接口异常，请检查'),$url,'error');
		}
		include_once($file);
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
			phpok_log(P_Lang('异步通知：订单信息不完整'));
			exit('error');
		}
		$rs = $this->model('order')->get_one_from_sn($sn);
		if(!$rs){
			phpok_log(P_Lang('异步通知：订单信息不存在'));
			exit('error');
		}
		if($rs['pay_end']){
			exit('success');
		}
		if(!$rs['pay_id']){
			phpok_log(P_Lang('异步通知：未指定支付方式'));
			exit('error');
		}
		$payment_rs = $this->model('payment')->get_one($rs['pay_id']);
		if(!$payment_rs){
			phpok_log(P_Lang('异步通知：付款方式不存在'));
			exit('error');
		}
		if(!$payment_rs['status']){
			phpok_log(P_Lang('异步通知：付款方式未启用'));
			exit('error');
		}
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/notify.php';
		if(!is_file($file)){
			phpok_log(P_Lang('异步通知：异步通知文件不存在'));
			exit('error');
		}
		include_once($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		exit('success');
	}

	private function auth_check()
	{
		if($_SESSION['user_id']){
			$id = $this->get('id','int');
			if(!$id){
				error(P_Lang('订单ID为空'),$this->url('order'),'error');
			}
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				error(P_Lang('订单信息不存在'),$this->url('order'),'error');
			}
			if($rs['user_id'] != $_SESSION['user_id']){
				error(P_Lang('您没有权限为此订单执行支付'),$this->url('order'),'error');
			}
			$error_url = $this->url('order','info','id='.$id);
		}else{
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				error(P_Lang('订单信息不完整，请检查'),$this->url('index'),'error');
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if(!$rs){
				error(P_Lang('订单信息不存在'),$this->url('index'),'error');
			}
			if($rs['passwd'] != $passwd){
				error(P_Lang('验证不通过'),$this->url('index'),'error');
			}
			$error_url = $this->url('order','info','sn='.$sn.'&passwd='.$passwd);
		}
		return array('rs'=>$rs,'error_url'=>$error_url);
	}
}

?>