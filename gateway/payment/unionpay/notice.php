<?php
/*****************************************************************************************
	文件： payment/unionpay/notice.php
	备注： 支付通知页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class unionpay_notice
{
	private $paydir;
	private $order;
	private $payment;
	public function __construct($order,$payment)
	{
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/unionpay/';
		$this->order = $order;
		$this->param = $payment;
		include_once($this->paydir."unionpay.php");
	}

	//获取订单信息
	public function submit()
	{
		$payment = new unionpay_lib();
		$payment->set_verify_id($GLOBALS['app']->dir_root.$this->param['param']['verify_cert_file']);
		if($_SESSION['user_id']){
			$array = array($GLOBALS['app']->config['ctrl_id'],$GLOBALS['app']->config['func_id'],'id');
		}else{
			$array = array($GLOBALS['app']->config['ctrl_id'],$GLOBALS['app']->config['func_id'],'sn','passwd');
		}
		$params = $_POST;
		if($params['respCode'] != '00'){
			error("付款失败，错误信息：".$params['respMsg'],'','error');
		}
		if($this->order['passwd'] != $params['reqReserved'] || !$params['reqReserved']){
			error('您没有权限查看此订单信息','','error');
		}
		$chk = $payment->verify($params);
		if(!$chk){
			error('付款签名验证失败，请登录支付平台检查','','error');
		}

		$pay_date = $GLOBALS['app']->time;
		$price = round(($params['settleAmt']/100),2);
		$array = array('pay_status'=>"付款完成",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
		$array['status'] = '付款完成';
		$data = array();
		$data['traceNo'] = $params['traceNo'];
		$data['traceTime'] = $params['traceTime'];
		$data['queryId'] = $params['queryId'];
		$data['currencyCode'] = $params['currencyCode'];
		$array['ext'] = serialize($data);
		$GLOBALS['app']->model('order')->save($array,$this->order['id']);
		return true;
	}
}
?>