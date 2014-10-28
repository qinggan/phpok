<?php
/*****************************************************************************************
	文件： payment/alipay/notice.php
	备注： 支付通知页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class alipay_notice
{
	var $paydir;
	var $order;
	var $payment;
	function __construct($order,$payment)
	{
		$this->paydir = $GLOBALS['app']->dir_root.'payment/alipay/';
		$this->order = $order;
		$this->payment = $payment;
		include_once($this->paydir."lib/alipay_notify.class.php");
	}

	//获取订单信息
	function submit()
	{
		$sn = $GLOBALS['app']->get('out_trade_no');
		//注销系统三个变量
		unset($_GET[$GLOBALS['app']->config['ctrl_id']],$_GET[$GLOBALS['app']->config['func_id']]);
		unset($_GET['id']);
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config = array('partner'=>$this->payment['param']['pid'],'key'=>$this->payment['param']['key']);
		$alipay_config['sign_type'] ='MD5';
		$alipay_config['input_charset']= 'utf-8';
		$alipay_config['cacert']    = $this->paydir.'cacert.pem';
		$alipay_config['transport']    = 'http';
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verify($_GET);
		if(!$verify_result)
		{
			error(P_Lang('订单验证不通过，请联系管理员确认'),$GLOBALS['app']->url,'error');
		}
		//附款日期
		$pay_date = $GLOBALS['app']->get('notify_time');
		if($pay_date) $pay_date = strtotime($pay_date);
		//附款金额，支付宝接口仅支持人民币
		$price = $GLOBALS['app']->get('total_fee','float');
		//更新订单信息
		$array = array('pay_status'=>"付款完成",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
		$array['status'] = '付款完成';
		//更新扩展数据
		$alipay = array();
		$alipay['buyer_email'] = $GLOBALS['app']->get('buyer_email');
		$alipay['buyer_id'] = $GLOBALS['app']->get('buyer_id');
		$alipay['time'] = $GLOBALS['app']->get('notify_time');
		$alipay['seller_email'] = $GLOBALS['app']->get('seller_email');
		$alipay['seller_id'] = $GLOBALS['app']->get('seller_id');
		$alipay['total_fee'] = $GLOBALS['app']->get('total_fee');
		$alipay['trade_no'] = $GLOBALS['app']->get('trade_no');
		$alipay['trade_status'] = $GLOBALS['app']->get('trade_status');
		$array['ext'] = serialize($alipay);
		$GLOBALS['app']->model('order')->save($array,$this->order['id']);
		return true;
	}
}
?>