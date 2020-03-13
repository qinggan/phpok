<?php
/**
 * Citcon 异步通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年2月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class citconpay_notify
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
			exit('SUCCESS');
		}
		$fields = $app->get('fields');
		if(!$fields){
			phpok_log('参数不完整');
			exit('error');
		}
		$status = $app->get('status');
		if($status){
			$status = strtolower($status);
		}
		if(!$status || $status != 'success'){
			phpok_log('订单未成功付款');
			exit('error');
		}
		$data = array();
		$data['fields'] = $fields;
		$tmplist = explode(",",$fields);
		foreach($tmplist as $key=>$value){
			$data[$value] = $app->get($value);
		}
		$citconpay = new citconpay_payment($this->param['param']['token_id']);
		$chkcode = $citconpay->sign_ipn($data);
		$notify_id = $app->get('notify_id');
		if(!$notify_id || !$chkcode || $chkcode != $notify_id){
			phpok_log('签名认证不通过');
			exit('error');
		}
		$p_array = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$p_array['id'] = $app->get('id');
		$p_array['amount'] = $app->get('amount');
		$p_array['status'] = $app->get('status');
		$p_array['currency'] = $app->get('currency');
		$p_array['time'] = $app->get('time');
		$p_array['reference'] = $app->get('reference');
		$p_array['notify_id'] = $app->get('notify_id');
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
		exit('SUCCESS');
	}
}