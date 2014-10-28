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
		$this->paydir = $GLOBALS['app']->dir_root."payment/chinapay/";
		include_once($this->paydir."chinapay.php");
	}

	function submit()
	{
		$chinapay = new chinapay_lib();
		$debug = $this->payment['param']['env'] == 1 ? false : true;
		$chinapay->set_debug($debug);
		$chinapay->set_pid($this->payment['param']['pid']);
		$chinapay->set_pri_key($this->payment['param']['prikey']);
		$chinapay->set_pub_key($this->payment['param']['pubkey']);
		$merid = $GLOBALS['app']->get('merid');
		$orderno = $GLOBALS['app']->get('orderno');
		$transdate = $GLOBALS['app']->get('transdate');
		$amount = $GLOBALS['app']->get('amount');
		$currencycode = $GLOBALS['app']->get('currencycode');
		$transtype = $GLOBALS['app']->get('transtype');
		$status = $GLOBALS['app']->get('status');
		$checkvalue = $GLOBALS['app']->get('checkvalue');
		$gateId = $GLOBALS['app']->get('gateId');
		$priv1 = $GLOBALS['app']->get('priv1');
		$opts = array('merid'=>$merid,'orderno'=>$orderno,'transdate'=>$transdate,'amount'=>$amount);
		$opts['currencycode'] = $currencycode;
		$opts['transtype'] = $transtype;
		$opts['status'] = $status;
		$opts['checkvalue'] = $checkvalue;
		$opts['gateId'] = $gateId;
		$opts['priv1'] = $priv1;
		$verify = $chinapay->verify($opts);
		if(!$verify)
		{
			phpok_log('答名验证不通过');
			exit;
		}
		if($status != '1001')
		{
			echo $status;
			exit;
		}
		$pay_date = $GLOBALS['app']->time;
		$price = $amount;
		//更新订单信息
		$array = array('pay_status'=>"付款完成",'pay_date'=>$pay_date,'pay_price'=>$price,'pay_end'=>1);
		$array['status'] = '付款完成';
		//更新扩展数据
		$tmp2 = array();
		$tmp2['orderno'] = $orderno;
		$tmp2['status'] = $status;
		$tmp2['amount'] = $amount;
		$array['ext'] = serialize($tmp2);
		$GLOBALS['app']->model('order')->save($array,$this->order['id']);
		exit('success');
	}

}
?>