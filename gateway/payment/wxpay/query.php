<?php
/*****************************************************************************************
	文件： gateway/payment/wxpay/query.php
	备注： 微信订单接口查询
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月09日 04时28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wxpay_query
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
		$this->obj = new wxpay_lib();
		$this->obj->config($this->param['param']);
	}

	public function submit()
	{
		global $app;
		$rs = $app->model('payment')->log_one($this->order['id']);
		if(!$rs){
			$app->error('订单信息不存在');
		}
		if($rs['status']){
			$app->success();
		}
		$data = $this->obj->query($this->order['sn'].'-'.$this->order['id']);
		if(!$data){
			$app->error('查询失败');
		}
		if($data['trade_state'] == 'SUCCESS'){
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
			if(!$this->order['status']){
				$array = array('status'=>1,'ext'=>serialize($ext));
				$app->model('payment')->log_update($array,$this->order['id']);
			}
			if($this->order['type'] == 'order'){
				$order = $app->model('order')->get_one_from_sn($this->order['sn']);
				if($order){
					$ext['log_id'] = $this->order['id'];
					//登记订单
					$payment_data = array();
					$payment_data['order_id'] = $order['id'];
					$payment_data['payment_id'] = $this->param['id'];
					$payment_data['title'] = $this->param['title'];
					$payment_data['price'] = $this->order['price']; //登记实付金额
					$payment_data['currency_id'] = $this->param['currency']['id']; //登记实付货币
					$payment_data['currency_rate'] = $this->param['currency']['val']; //登记的汇率
					$payment_data['startdate'] = $app->time; //登记时间
					$payment_data['dateline'] = $app->time; //付款时间
					$payment_data['ext'] = serialize($ext);
					$app->model('order')->save_payment($payment_data);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
			if($this->order['type'] == 'recharge'){
				$app->model('wealth')->recharge($this->order['id']);
			}
			$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
			$array = array('status'=>$data['trade_state']);
			$app->success($array);
		}
		$array = array('status'=>$data['trade_state'],'content'=>$data['trade_state_desc']);
		$app->error($array);
	}
}