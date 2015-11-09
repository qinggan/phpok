<?php
/*****************************************************************************************
	文件： gateway/payment/wxpay/submit.php
	备注： 微信支付
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月04日 11时46分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wxpay_submit
{
	private $order;
	private $param;
	private $obj;
	//order，订单信息
	//param，微信支付配置信息
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/wxpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include "wxpay.php";
		$this->obj = new wxpay_lib();
		$this->obj->app_id($this->param['param']['appid']);
		$this->obj->mch_id($this->param['param']['mch_id']);
		$this->obj->app_key($this->param['param']['app_key']);
		$this->obj->app_secret($this->param['param']['app_secret']);
		$this->obj->ssl_cert($this->param['param']['pem_cert']);
		$this->obj->ssl_key($this->param['param']['pem_key']);
		$this->obj->proxy_host($this->param['param']['proxy_host']);
		$this->obj->proxy_port($this->param['param']['proxy_port']);
	}

	public function submit()
	{
		$data = array('body'=>'订单：'.$this->order['sn']);
		$data['detail'] = '订单：'.$this->order['sn'];
		$data['out_trade_no'] = $this->order['sn'];
		$data['total_fee'] = intval($this->order['price']*100);
		$data['notify_url'] = $this->baseurl."gateway/payment/wxpay/notify_url.php";
		$data['trade_type'] = strtoupper($this->param['param']['trade_type']);
		$data['product_id'] = $this->order['sn'];
		$info = $this->obj->pay_url($data);
		if(!$info){
			error('支付出错，请联系管理员');
		}
		$this->head();
		$info = rawurlencode($info);
		echo <<<EOT
<div class="main">
	<h3>请用微信扫一扫</h3>
	<div class="qrcode"><img src="{$this->baseurl}gateway/payment/wxpay/qrcode.php?data={$info}" border="0" /></div>
</div>
EOT;
		$this->foot();
	}

	private function head()
	{
		$jsurl = $GLOBALS['app']->url('js');
		echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt"> 
	<title>微信扫一扫</title>
	<style type="text/css">
	body{width:900px;margin:0 auto;}
	.main{width:300px;position:relative;margin:20% auto;}
	.main h3{text-align:center;}
	.main .qrcode{text-align:center;}
	</style>
	<script type="text/javascript" src="{$jsurl}"></script>
</head>
<body>
EOT;
	}

	private function foot()
	{
		$ajaxurl = $GLOBALS['app']->url('payment','query','sn='.$this->order['sn'],'api');
		$gourl = $GLOBALS['app']->url('payment','show','id='.$this->order['id'],'www');
		echo <<<EOT
<script type="text/javascript">
function pending()
{
	jQuery.ajax({
		"url":"{$ajaxurl}",
		"dataType":"json",
		"cache":false,
		"async":true,
		"beforeSend": function (XMLHttpRequest){
			XMLHttpRequest.setRequestHeader("request_type","ajax");
		},
		"success":function(rs){
			if(rs.status == "ok"){
				window.location.href = "{$gourl}";
			}else{
				window.setTimeout("pending()", 2000);
			}
		}
	});
}
$(document).ready(function(){
	window.setTimeout("pending()", 5000);
});
</script>
EOT;
		echo '</body>'."\n";
		echo '</html>';
	}
}
?>