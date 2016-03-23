<?php
/*****************************************************************************************
	文件： payment/chinapay/notify.php
	备注： 异步通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月4日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class chinapay_notify
{
	var $paydir;
	var $order;
	var $payment;
	function __construct($order,$payment)
	{
		$this->order = $order;
		$this->payment = $payment;
		$this->paydir = $GLOBALS['app']->dir_root."gateway/payment/chinapay/";
		include_once($this->paydir."chinapay.php");
	}

	function submit()
	{
		global $app;
		$chinapay = new chinapay_lib();
		$debug = $this->payment['param']['env'] == 'start' ? false : true;
		$chinapay->set_debug($debug);
		$chinapay->set_pid($this->payment['param']['pid']);
		$chinapay->set_pri_key($this->payment['param']['prikey']);
		$chinapay->set_pub_key($this->payment['param']['pubkey']);
		$merid = $app->get('merid');
		$orderno = $app->get('orderno');
		$transdate = $app->get('transdate');
		$amount = $app->get('amount');
		$currencycode = $app->get('currencycode');
		$transtype = $app->get('transtype');
		$status = $app->get('status');
		$checkvalue = $app->get('checkvalue');
		$gateId = $app->get('gateId');
		$priv1 = $app->get('priv1');
		$opts = array('merid'=>$merid,'orderno'=>$orderno,'transdate'=>$transdate,'amount'=>$amount);
		$opts['currencycode'] = $currencycode;
		$opts['transtype'] = $transtype;
		$opts['status'] = $status;
		$opts['checkvalue'] = $checkvalue;
		$opts['gateId'] = $gateId;
		$opts['priv1'] = $priv1;
		$verify = $chinapay->verify($opts);
		if(!$verify){
			exit('fail');
		}
		if($status != '1001'){
			exit($status);
		}
		$tmparray = array('merid'=>$merid,'orderno'=>$orderno,'transdate'=>$transdate,'amount'=>$amount);
		$tmparray['currencycode'] = $currencycode;
		$tmparray['transtype'] = $transtype;
		$tmparray['status'] = $status;
		$tmparray['checkvalue'] = $checkvalue;
		$tmparray['gateId'] = $gateId;
		$tmparray['priv1'] = $priv1;
		$array = array('status'=>1,'ext'=>serialize($tmparray));
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
				$array['ext'] = serialize($tmparray);
				$order_payment = $app->model('order')->order_payment($order['id']);
				if(!$order_payment){
					$app->model('order')->save_payment($array);
				}else{
					$app->model('order')->save_payment($array,$order_payment['id']);
				}
			}
		}
		exit('success');
	}

}
?>