<?php
/*****************************************************************************************
	文件： payment/submit.php
	备注： Paypal支付提交页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月24日 09时56分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class paypal_submit
{
	public $param;
	public $order;
	public $baseurl;
	public $paydir;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/paypal/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."lib/paypal.php");
	}

	//执行提交按钮
	public function submit()
	{
        $notify_url = $this->baseurl."gateway/payment/paypal/notify_url.php";
        $return_url = $GLOBALS['app']->url('payment','notice','id='.$this->order['id'],'www',true);
        $cancel_url = $GLOBALS['app']->url('payment','show','id='.$this->order['id'],'www',true);
		$paypal = new paypal_payment($this->param['param']["payid"],$this->param['param']["at"]);
        $price = price_format_val($this->order['price'],$this->order['currency_id'],$this->param['currency']['id']);
        $paypal->set_value("amount",$price);
		$currency = $this->param['currency']['code'];
		$paypal->set_value("currency",$currency);
		$paypal->set_value("ordersn",$this->order["sn"].'-'.$this->order['id']);
		$paypal->set_value("action_url",$this->param['param']["action"]);
		$paypal->set_value("return_url",$return_url);//成功返回
		$paypal->set_value("cancel_return",$cancel_url);//取消退出
		$paypal->set_value("notify_url",$notify_url);//订单成功后发送给网站的信息
		$htmlbutton = $paypal->create_button();
		echo '<!DOCTYPE html>'."\n";
		echo '<html>'."\n";
		echo '<head>'."\n\t";
		echo '<meta charset="utf-8" />'."\n\t";
		echo '<title>付款中</title>'."\n";
		echo '</head>'."\n<body>\n";
		echo '<div class="div"><p>正在跳转至Paypal，请稍候...</p><input type="button" value="提交" onclick="go_paypal()"/></div>'."\n";
		echo '<div style="display:none">'.$htmlbutton."</div>\n";
		echo '<script type="text/javascript">'."\n";
		echo 'function go_paypal(){'."\n";
		echo 'document.getElementById("paypalform").submit();'."\n";
		echo 'return true;'."\n";
		echo '}'."\n";
		echo 'go_paypal();'."\n";
		echo '</script>'."\n";
		echo "\n".'</body>'."\n</html>";
		exit;
	}
}
?>