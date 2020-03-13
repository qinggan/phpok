<?php
/**
 * 支付同步通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年2月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class citconpay_notice
{
	public $paydir;
	public $order;
	public $payment;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/citconpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."citcon.php");
	}

	public function submit()
	{
		global $app;
		if($this->order['status']){
			return true;
		}
		$citconpay = new citconpay_payment($this->param['param']["token_id"]);
		$citconpay->sn($this->order['sn'].'-'.$this->order['id']);
		$data = $citconpay->query();
		if(!$data){
			return false;
		}
		if($data['status'] != 'success'){
			return false;
		}
		$p_array = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$p_array['id'] = $data['id'];
		$p_array['amount'] = $data['amount'];
		$p_array['status'] = $data['status'];
		$p_array['currency'] = $data['currency'];
		$p_array['time'] = $$data['time'];
		$p_array['reference'] = $data['reference'];
		$array = array('status'=>1,'ext'=>serialize($p_array));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($p_array));
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge' && $p_array['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}