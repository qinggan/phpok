<?php
/*****************************************************************************************
	文件： payment/tenpay/notice.php
	备注： 支付通知页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tenpay_notice
{
	var $paydir;
	var $order;
	var $payment;
	function __construct($order,$payment)
	{
		$this->paydir = $GLOBALS['app']->dir_root.'payment/tenpay/';
		$this->order = $order;
		$this->payment = $payment;
		include_once($this->paydir."tenpay.php");
	}

	//获取订单信息
	function submit()
	{
		$tenpay = new tenpay_lib();
		$tenpay->set_key($this->payment['param']['key']);
		$array = array($GLOBALS['app']->config['ctrl_id'],$GLOBALS['app']->config['func_id'],'sign','id');
		$trade_mode = $GLOBALS['app']->get('trade_mode','int');
		$trade_status = $GLOBALS['app']->get('trade_state','int');
		//检测为fail的几种情况
		if($trade_mode != '1' && $trade_mode != '2')
		{
			error('订单错误：参数传递错误！','','error');
		}
		if($trade_mode == '1')
		{
			if($trade_status != '0')
			{
				error('订单错误：付款失败！','','error');
			}
		}
		$attach = $GLOBALS['app']->get('attach');
		if($this->order['passwd'] != $attach)
		{
			error('您没有权限查看此订单信息','','error');
		}
		
		//回调验证不成功时，直接中止
		if(!$tenpay->check_sign($array))
		{
			error('验证不通过，请检查','','error');
		}
		
		if($GLOBALS['app']->get('retcode','int') != '0')
		{
			error('付款失败，请检查','','error');
		}
		$pay_date = $tenpay->get_date();
		if($pay_date) $pay_date = strtotime($pay_date);
		$price = round(($tenpay->param('total_fee') / 100),2);
		//更新订单信息
		$array = array('pay_status'=>"已付款",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
		$array['status'] = "付款完成";
		//更新扩展数据
		$info = array();
		$info['fee_type'] = $tenpay->param('fee_type');
		$info['notify_id'] = $tenpay->param('notify_id');
		$info['time_end'] = $tenpay->param('time_end');
		$info['total_fee'] = $tenpay->param('total_fee');
		$info['transaction_id'] = $tenpay->param('transaction_id');
		$array['ext'] = serialize($info);
		$GLOBALS['app']->model('order')->save($array,$this->order['id']);
		return true;
	}
}
?>