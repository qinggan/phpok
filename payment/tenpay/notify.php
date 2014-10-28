<?php
/*****************************************************************************************
	文件： payment/tenpay/notify.php
	备注： 异步通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tenpay_notify
{
	var $paydir;
	var $order;
	var $payment;
	function __construct($order,$payment)
	{
		$this->order = $order;
		$this->payment = $payment;
		$this->paydir = $GLOBALS['app']->dir_root."payment/tenpay/";
		include_once($this->paydir."tenpay.php");
	}

	function submit()
	{
		$tenpay = new tenpay_lib();
		$tenpay->set_key($this->payment['param']['key']);
		//签名验证
		//设置不包含的参数
		$array = array($GLOBALS['app']->config['ctrl_id'],$GLOBALS['app']->config['func_id'],'sign','sn');
		$trade_mode = $GLOBALS['app']->get('trade_mode','int');
		$trade_status = $GLOBALS['app']->get('trade_state','int');
		//检测为fail的几种情况
		if($trade_mode != '1' && $trade_mode != '2')
		{
			phpok_log('支付模式不符合要求');
			exit('fail');
		}
		if($trade_mode == '1')
		{
			if($trade_status != '0')
			{
				phpok_log('订单失败');
				exit('fail');
			}
		}
		$attach = $GLOBALS['app']->get('attach');
		

		//密码不一致时，返回错误
		if($this->order['passwd'] != $attach || !$attach)
		{
			phpok_log('订单密码验证不通过');
			exit('fail');
		}
		
		//初始化内容参数
		$tenpay->param_clear();
		$notify_id = $GLOBALS['app']->get('notify_id');
		//通过通知ID查询，确保通知来至财付通
		$tenpay->set_url('https://gw.tenpay.com/gateway/simpleverifynotifyid.xml');
		$tenpay->param('partner',$this->payment['param']['pid']);
		$tenpay->param('notify_id',$notify_id);
		$tenpay->set_key($this->payment['param']['key']);
		$url = $tenpay->url();
		$call = $tenpay->call($url);
		//请求数据失败
		if(!$call)
		{
			phpok_log('财付通请求不成功，请检查');
			exit('fail');
		}
		$tenpay->param_clear();
		$tenpay->set_key($this->payment['param']['key']);
		$tenpay->set_xml_content();
		//取得订单通知
		if(!$tenpay->check_sign($array))
		{
			phpok_log('验证不通过，请检查');
			exit('fail');
		}
		if($tenpay->param('retcode') == '0')
		{
			$pay_date = $tenpay->get_date();
			$pay_date = $pay_date ? strtotime($pay_date) : $GLOBALS['app']->time;

			$price = round(($tenpay->param('total_fee') / 100),2);
			//更新订单信息
			$array = array('pay_status'=>"付款完成",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
			$array['status'] = '付款完成';
			//更新扩展数据
			$tenpay = array();
			$tenpay['fee_type'] = $tenpay->param('fee_type');
			$tenpay['notify_id'] = $tenpay->param('notify_id');
			$tenpay['time_end'] = $tenpay->param('time_end');
			$tenpay['total_fee'] = $tenpay->param('total_fee');
			$tenpay['transaction_id'] = $tenpay->param('transaction_id');
			$array['ext'] = serialize($tenpay);
			$GLOBALS['app']->model('order')->save($array,$this->order['id']);
			exit('success');
		}
		exit('success');
	}
}
?>