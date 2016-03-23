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
		$data['body'] = '订单：'.$this->order['sn'];
		$data['detail'] = '订单：'.$this->order['sn'];
		$data['out_trade_no'] = $this->order['sn'];
		$data['total_fee'] = intval($this->order['price']*100);
		$data['notify_url'] = $this->baseurl."gateway/payment/wxpay/notify_url.php";
		$info = $wxpay->create($data);
		if(!$info){
			error('支付出错，请联系管理员');
		}
		if($wxpay->trade_type() == 'jsapi'){
			$this->head();
			//$config = $wxpay->GetJsApiParameters($info);
			$config = $wxpay->get_jsapi_param($info);
			$gourl = $this->param['param']['jsapi_link'];
			if(!$gourl){
				$gourl = $GLOBALS['app']->url('order','info');
			}
			$gourl .= strpos($gourl,'?') !== false ? '&sn='.$this->order['sn'] : '?sn='.$this->order['sn'];
			echo <<<EOT
<script type="text/javascript" src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
function callpay()
{
	wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: '{$config[appId]}', // 必填，公众号的唯一标识
        timestamp:'{$config[timeStamp]}' , // 必填，生成签名的时间戳
        nonceStr: '{$config[nonceStr]}', // 必填，生成签名的随机串
        signature: '{$config[sign]}',// 必填，签名，见附录1
        jsApiList: ['chooseWXPay'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });
    wx.ready(function(){
        wx.chooseWXPay({
            timestamp: '{$config[timeStamp]}', // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
            nonceStr: '{$config[nonceStr]}', // 支付签名随机串，不长于 32 位
            package: '{$config[package]}', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
            signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
            paySign: '{$config[paySign]}', // 支付签名
            success: function (res) {
	            var url = "{$gourl}";
	            $.phpok.go(url);
            }
        });
    });
}
$(document).ready(function(){
	callpay();
});
</script>
EOT;
		}elseif($wxpay->trade_type() == 'native'){
			$this->head('请用微信扫一扫');
			echo '<div class="main"><h3>请用微信扫一扫</h3>';
			echo '<div class="qrcode"><img src="'.$this->baseurl.'gateway/payment/wxpay/qrcode.php?data='.$info['code_url'].'" border="0" /></div></div>';
			$this->foot();
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