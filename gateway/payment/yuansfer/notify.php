<?php
/**
 * 异步通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月13日
**/
use Yuansfer\Yuansfer;
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class yuansfer_notify
{
	var $paydir;
	var $order;
	var $payment;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/yuansfer/';
		$this->baseurl = $GLOBALS['app']->url;
		require_once($this->paydir."vendor/autoload.php");
	}

	public function submit()
	{
		global $app;
		$config = array(
			Yuansfer::MERCHANT_NO => $this->param['param']['merchantNo'],
			Yuansfer::STORE_NO => $this->param['param']['storeNo'],
			Yuansfer::API_TOKEN => $this->param['param']['yuansferToken'],
			Yuansfer::TEST_API_TOKEN => $this->param['param']['yuansferToken'],
		);
		$yuansfer = new Yuansfer($config);
		if($this->param['param']['ptype'] == 'demo'){
			$yuansfer->setTestMode();
		}else{
			$yuansfer->setProductionMode();
		}
		if (!$yuansfer->verifyIPN()) {
		    header('HTTP/1.1 503 Service Unavailable', true, 503);
		    exit;
		}
		if($_POST['status'] !== 'success'){
		    header('HTTP/1.1 503 Service Unavailable', true, 503);
		    exit;
		}
		$price = $app->get('amount','float');
		$array = array('status'=>1);
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		//如果当前支付操作是订单
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time);
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
		if($this->order['type'] == 'recharge'){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		exit('success');
	}
}