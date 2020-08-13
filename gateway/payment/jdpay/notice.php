<?php
/**
 * 京东支付同步通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年5月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class jdpay_notice
{
	var $paydir;
	var $order;
	var $payment;
	public function __construct($order,$param)
	{
		global $app;
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $app->dir_root.'gateway/payment/jdpay/';
		$this->baseurl = $app->url;
		include_once($this->paydir."jdpay.class.php");
	}

	//获取订单信息
	public function submit()
	{
		global $app;
		//如果异步通知已通验证，同步通知就不需要再次验收
		if($this->order['status']){
			return true;
		}
		$config = array();
		if($this->param && $this->param['param']){
			if(is_string($this->param['param'])){
				$config = unserialize($this->param['param']);
			}else{
				$config = $this->param['param'];
			}
		}
		$jdpay = new jdpay_lib($config);
		$data = array();
		$data['note'] = $app->get('note');
		$data['tradeNum'] = $app->get('tradeNum');
		$data['status'] = $app->get('status');
		$data['tradeTime'] = $app->get('tradeTime');
		$data['amount'] = $app->get('amount');
		$data['currency'] = $app->get('currency');
		$sign = $app->get('sign');
		$verify_result = $jdpay->verify($data,$sign);
		if(!$verify_result){
			$app->error(P_Lang('订单验证不通过，请联系管理员确认'),$app->url);
		}
		$pay_date = $app->time;
		$price = $app->get('amount','intval');
		if($price){
			$price = round($price/100,2);
		}
		//更新扩展数据
		$ext = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		foreach($data as $key=>$value){
			$ext[$key] = $value;
		}
		//更新支付记录
		$array = array('status'=>1,'ext'=>serialize($ext));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		//如果当前支付操作是订单
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($ext));
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		//充值操作
		if($this->order['type'] == 'recharge' && $ext['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}