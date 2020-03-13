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
		echo '<!DOCTYPE html><html><head><title>Paypal</title></head><body>';
        $htmlbutton  = $this->get_html();
		$htmlbutton .= "\n";
		$htmlbutton .= '<script type="text/javascript">'."\n";
		$htmlbutton .= 'document.getElementById("paypalform").submit();'."\n";
		$htmlbutton .= '</script>'."\n";
		echo $htmlbutton;
		echo '</body></html>';
		exit;
	}

	private function get_html()
	{
		$notify_url = $this->baseurl."gateway/payment/paypal/notify_url.php";
        $return_url = $GLOBALS['app']->url('payment','notice','id='.$this->order['id'],'www',true);
        $cancel_url = $GLOBALS['app']->url('payment','show','id='.$this->order['id'],'www',true);
		$paypal = new paypal_payment($this->param['param']["payid"],$this->param['param']["at"]);
		$currency = $GLOBALS['app']->model('currency')->get_one($this->param['currency_id']);
        $price = price_format_val($this->order['price'],$this->order['currency_id'],$this->param['currency_id']);
        $paypal->set_value("amount",$price);
		$paypal->set_value("currency",$currency['code']);
		$paypal->set_value("ordersn",$this->order["sn"].'-'.$this->order['id']);
		$paypal->set_value("action_url",$this->param['param']["action"]);
		$paypal->set_value("return_url",$return_url);//成功返回
		$paypal->set_value("cancel_return",$cancel_url);//取消退出
		$paypal->set_value("notify_url",$notify_url);//订单成功后发送给网站的信息
		$html = $paypal->create_button();
		return $html;
	}

	public function select()
	{
		global $app;
		$html = $this->get_html();
		$app->tpl->assign('htmlinfo',$html);
		if($app->tpl->check_exists('payment/paypal/html')){
			return $app->tpl->fetch('payment/paypal/html');
		}
		if(file_exists($app->dir_gateway.'payment/paypal/html.html')){
			return $app->tpl->fetch($app->dir_gateway.'payment/paypal/html.html','abs-file');
		}
		return $html;
	}
}