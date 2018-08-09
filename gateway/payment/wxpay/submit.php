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
	}

	public function submit()
	{
		global $app;
		$wxpay = new wxpay_lib();
		$wxpay->config($this->param['param']);
		$data = array();
		if($wxpay->trade_type() == 'jsapi'){
			$openid = $wxpay->get_openid();
			if(!$openid){
				exit('获取OpenId失败，请检查 '.$wxpay->errmsg());
			}
			$data['openid'] = $openid;
		}else{
			$data['product_id'] = $this->order['sn'];
		}
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one($this->order['sn'],'sn');
			$data['attach'] = $order['passwd'];
		}
		$price = price_format_val($this->order['price'],$this->order['currency_id'],$this->param['currency']['id']);
		$data['body'] = '订单：'.$this->order['sn'].'-'.$this->order['id'];
		$data['out_trade_no'] = $this->order['sn'].'-'.$this->order['id'];
		$data['total_fee'] = intval($price*100);
		$data['notify_url'] = $this->baseurl."gateway/payment/wxpay/notify_url.php";
		$info = $wxpay->create($data);
		if(!$info){
			$app->error('支付出错，请联系管理员');
		}
		if(strtolower($info['result_code']) == 'fail'){
			$app->error($info['err_code'].'：'.$info['err_code_des']);
		}
		$app->assign('info',$info);
		$app->assign('data',$data);
		$app->assign('order',$this->order);
		$app->assign('price_rmb',$price);
		$rs = $app->model('order')->get_one($this->order['sn'],'sn');
		$app->assign('rs',$rs);
		$ajaxurl = $app->url('payment','query','sn='.$this->order['sn'].'-'.$this->order['id'],'api');
		$app->assign('ajaxurl',$ajaxurl);
		if($wxpay->trade_type() == 'wap'){
			$config = $wxpay->get_jsapi_param($info);
			$app->assign('wxconfig',$config);
			$string = 'appid='.$config['appId'].'&timestamp='.$config['timeStamp'].'&noncestr='.$config['nonceStr'];
			$string.= "&package=WAP&prepayid=".$config['prepay_id']."&sign=".$config['sign'];
			$url = "weixin://wap/pay?".rawurlencode($string);
			$app->assign('wxpay_link',$url);
			$app->tpl->display("payment/wxpay/submit_wap");
		}
		if($wxpay->trade_type() == 'jsapi'){
			$config = $wxpay->get_jsapi_param($info);
			$app->assign('wxconfig',$config);
			$app->tpl->display("payment/wxpay/submit_jsapi");
		}elseif($wxpay->trade_type() == 'native'){
			$app->tpl->display('payment/wxpay/submit_qrcode');
		}
	}

	private function head($title='')
	{
		$jsurl = $GLOBALS['app']->url('js');
		echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt"> 
	<title>{$title}</title>
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