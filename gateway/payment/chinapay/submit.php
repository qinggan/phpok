<?php
/*****************************************************************************************
	文件： payment/chinapay/submit.php
	备注： 提交支付接口
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月4日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class chinapay_submit
{
	//支付接口初始化
	var $param;
	var $order;
	var $paydir;
	var $baseurl;
	var $dir_root;
	function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/chinapay/';
		$this->baseurl = $GLOBALS['app']->url;
		$this->dir_root = $GLOBALS['app']->dir_root;
		include_once($this->paydir."chinapay.php");
        if(!$this->param['param']['pid']){
	        error(P_Lang('未指定Chinapay的商户号，请联系管理员'),'','error');
        }
	}

	function param($param)
	{
		$this->param = $param;
	}

	function order($order)
	{
		$this->order = $order;
	}

	//创建订单
	function submit()
	{
        $notify_url = $this->baseurl."gateway/payment/chinpay/notify_url.php";
        $return_url = $GLOBALS['app']->url('payment','notice','id='.$this->order['id'],'www',true);
        $show_url = $GLOBALS['app']->url('payment','show','id='.$this->order['id'],'www',true);
        $currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
        $total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
        $debug = $this->param['param']['env'] == 'start' ? false : true;
        $chinapay = new chinapay_lib($this->dir_root);
        $chinapay->set_debug($debug);
        $chinapay->set_pid($this->param['param']['pid']);
        $chinapay->set_pri_key($this->param['param']['prikey']);
        $chinapay->set_pub_key($this->param['param']['pubkey']);
        $chinapay->set_orderid($this->order['id']);
        $options = array('notify_url'=>$notify_url,'return_url'=>$return_url,'bankid'=>$bankid,'price'=>$total_fee);
        $options['show_url'] = $show_url;
        $options['currency'] = $this->param['currency']['code'];
        $options['date'] = date("Ymd",$GLOBALS['app']->time);
        $options['passwd'] = $this->order['sn'];
        $chinapay->set_options($options);
        $info = $chinapay->action_form('paymentsubmit');
        if(!$info){
	        error(P_Lang('银行支付接口异常，数据未正常配置'),'','error');
        }
		//建立请求
		echo '<!DOCTYPE html>'."\n";
		echo '<html>'."\n";
		echo '<head>'."\n\t";
		echo '<meta charset="utf-8" />'."\n\t";
		echo '<title>付款中</title>'."\n";
		echo '</head>'."\n<body>\n";
		echo $info;
		echo '<script type="text/javascript">document.getElementById("paymentsubmit").submit()</script>';
		echo "\n".'</body>'."\n</html>";
		exit;
	}

}
?>