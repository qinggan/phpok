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
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('fail');
		}
		include_once($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
	}

	public function notice_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang("无法获取订单信息"),$this->url,'error');
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			error(P_Lang('订单信息为空'),$this->url,'error');
		}
		$burl = $this->url("order",'info','id='.$rs['id']);
		if(!$_SESSION['user_id']){
			$burl = $this->url("order","info","sn=".$rs['sn']."&passwd=".$rs['passwd']);
		}
		$burl = $this->config['www_file'].substr($burl,strlen($this->config['api_file']));
		if($rs['pay_end']){
			error(P_Lang('您的订单付款成功，请稍候，系统将引导您查看订单信息'),$burl,'ok');
		}
		$payment_rs = $this->model('payment')->get_one($rs['pay_id']);
		if(!$payment_rs){
			error(P_Lang('付款方案不存在'),$this->url,'error');
		}
		$file = $this->dir_root.'payment/'.$payment_rs['code'].'/notice.php';
		if(!is_file($file)){
			error(P_Lang('支付接口异常，请检查'),$this->url,'error');
		}
		include_once($file);
		$name = $payment_rs['code'].'_notice';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		error(P_Lang('您的订单付款成功，请稍候，系统将引导您查看订单信息'),$burl,'ok');
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