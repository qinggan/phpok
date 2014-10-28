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
	function __construct()
	{
		parent::control();
	}

	//提交支付
	function submit_f()
	{
		$rs = $this->auth_check();
		$error_url = $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
		if($rs['pay_end'])
		{
			error(P_Lang('该订单已结束，不能再执行付款操作'),$error_url,'error');
		}
		$payment = $this->get('payment','int');
		if(!$payment)
		{
			error(P_Lang('未指定付款方式'),$error_url,"error");
		}
		$payment_rs = $this->model('payment')->get_one($payment);
		//进入支付页
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/submit.php';
		if(!is_file($file))
		{
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
		if($currency)
		{
			$currency_rs = $this->model('currency')->get_one($currency);
			$currency_code = $currency_rs['code'];
			$pay_currency_rate = $currency_rs['val'];
		}
		else
		{
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

	//权限验证
	function auth_check()
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