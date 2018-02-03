<?php
/*****************************************************************************************
	文件： gateway/payment/wxpay/notify.php
	备注： 异步通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月09日 03时35分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wxpay_notify
{
	private $order;
	private $param;
	private $obj;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/wxpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include "wxpay.php";
	}

	public function submit()
	{
		global $app;
		$wxpay = new wxpay_lib();
		$wxpay->config($this->param['param']);
		$xml = $app->get('xml','html');
		$data = $app->lib('xml')->read('<root>'.$xml.'</root>',false);
		$rs = $app->model('payment')->log_check($data['out_trade_no']);
		if(!$rs){
			$this->error('订单信息不存在');
		}
		if($rs['status']){
			$this->ok();
		}
		//验证接收的信息是否符合要求
		$sign = $wxpay->create_sign($data);
		if($sign != $data['sign']){
			$this->error('签名验证不通过');
		}
		//保存订单信息
		$ext = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$ext['openid'] = $data['openid'];
		$ext['trade_type'] = $data['trade_type'];
		$ext['bank_type'] = $data['bank_type'];
		$ext['total_fee'] = $data['total_fee'];
		if($data['fee_type']){
			$ext['fee_type'] = $data['fee_type'];
		}
		if($data['cash_fee']){
			$ext['cash_fee'] = $data['cash_fee'];
			if($data['cash_fee_type']){
				$ext['cash_fee_type'] = $data['cash_fee_type'];
			}
		}
		if($data['coupon_fee']){
			$ext['coupon_fee'] = $data['coupon_fee'];
			if($data['coupon_count']){
				$ext['coupon_count'] = $data['coupon_count'];
			}
		}
		$ext['transaction_id'] = $data['transaction_id'];
		$ext['time_end'] = $data['time_end'];
		$tmp = $data['time_end'];
		$time = substr($tmp,0,4).'-'.substr($tmp,4,2).'-'.substr($tmp,6,2).' '.substr($tmp,8,2);
		$time.= ':'.substr($tmp,10,2).':'.substr($tmp,12,2);
		$mytime = strtotime($time);
		$payment_data = array('dateline'=>$mytime,'status'=>1,'ext'=>serialize($ext));
		$app->model('payment')->log_update($payment_data,$rs['id']);
		if($rs['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($rs['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$mytime,'ext'=>serialize($ext));
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
				}else{
					$payment = $app->model('payment')->get_one($rs['payment_id']);
					$data2 = array('order_id'=>$order['id'],'payment_id'=>$rs['payment_id']);
					$data2['title'] = $payment['title'];
					$data2['price'] = round($data['total_fee'],100);
					$data2['startdate'] = $mytime;
					$data2['dateline'] = $mytime;
					$data2['ext'] = serialize($ext);
					$app->model('order')->save_payment($data2);
				}
				//更新订单日志
				$app->model('order')->update_order_status($order['id'],'paid');
				$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
				$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
				$app->model('order')->log_save($log);
			}
		}
		if($this->order['type'] == 'recharge' && $ext['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$app->plugin('payment-notify',$this->order['id']);
		$this->ok();		
	}

	private function error($title)
	{
		header("Content-type:text/xml");
		$info = '<xml>';
		$info.= '<return_code><![CDATA[FAIL]]></return_code>';
		$info.= '<return_msg><![CDATA['.$title.']]></return_msg>';
		$info.= '</xml>';
		exit($info);
	}

	private function ok()
	{
		header("Content-type:text/xml");
		$info = '<xml>';
		$info.= '<return_code><![CDATA[SUCCESS]]></return_code>';
		$info.= '<return_msg><![CDATA[OK]]></return_msg>';
		$info.= '</xml>';
		exit($info);
	}
}
?>