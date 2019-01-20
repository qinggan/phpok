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
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/tenpay/';
		$this->order = $order;
		$this->payment = $payment;
		include_once($this->paydir."tenpay.php");
	}

	//获取订单信息
	function submit()
	{
		if($this->order['status']){
			return true;
		}
		global $app;
		$tenpay = new tenpay_lib();
		$tenpay->set_key($this->payment['param']['key']);
		$array = array($app->config['ctrl_id'],$app->config['func_id'],'sign','id');
		$trade_mode = $app->get('trade_mode','int');
		$trade_status = $app->get('trade_state','int');
		//检测为fail的几种情况
		if($trade_mode != '1' && $trade_mode != '2'){
			$app->error('订单错误：参数传递错误！');
		}
		if($trade_mode == '1'){
			if($trade_status != '0'){
				$app->error('订单错误：付款失败！');
			}
		}
		$attach = $app->get('attach');
		if(!$attach){
			$app->error('您没有权限查看此订单信息');
		}
		if(!$tenpay->check_sign($array)){
			$app->error('验证不通过，请检查');
		}
		if($app->get('retcode','int') != '0'){
			$app->error('付款失败，请检查');
		}

		$pay_date = $tenpay->get_date();
		$pay_date = $pay_date ? strtotime($pay_date) : $app->time;
		$price = round(($tenpay->param('total_fee') / 100),2);
		$tenpay = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$tenpay['fee_type'] = $tenpay->param('fee_type');
		$tenpay['notify_id'] = $tenpay->param('notify_id');
		$tenpay['time_end'] = $tenpay->param('time_end');
		$tenpay['total_fee'] = $tenpay->param('total_fee');
		$tenpay['transaction_id'] = $tenpay->param('transaction_id');
		$array = array('status'=>1,'ext'=>serialize($tenpay));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$app->model('order')->update_order_status($order['id'],'paid');
				$param = 'id='.$order['id']."&status=paid";
				$app->model('task')->add_once('order',$param);
				$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
				$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
				$app->model('order')->log_save($log);
				//增加order_payment
				$array = array('order_id'=>$order['id'],'payment_id'=>$this->param['id']);
				$array['title'] = $this->param['title'];
				$array['price'] = $price;
				$array['dateline'] = $app->time;
				$array['ext'] = serialize($tenpay);
				$order_payment = $app->model('order')->order_payment($order['id']);
				if(!$order_payment){
					$app->model('order')->save_payment($array);
				}else{
					$app->model('order')->save_payment($array,$order_payment['id']);
				}
			}
		}
		if($this->order['type'] == 'recharge' && $tenpay['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}