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

	public function submit($json=false)
	{
		global $app;
		$wxpay = new wxpay_lib();
		$wxpay->config($this->param['param']);
		$data = array();
		if($wxpay->trade_type() == 'jsapi' || $wxpay->trade_type() == 'miniprogram'){
			if($wxpay->trade_type() == 'miniprogram'){
				$openid = $app->session->val('wx_openid');
			}else{
				$openid = $wxpay->get_openid();
			}
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
		$rs = $app->model('order')->get_one($this->order['sn'],'sn');
		$app->assign('rs',$rs);
		$price = price_format_val($this->order['price'],$this->order['currency_id'],$this->param['currency']['id']);
		$data['body'] = '订单：'.$this->order['sn'].'-'.$this->order['id'];
		$data['out_trade_no'] = $this->order['sn'].'-'.$this->order['id'];
		$data['total_fee'] = intval($price*100);
		$data['notify_url'] = $this->baseurl."gateway/payment/wxpay/notify_url.php";
		//针对小程序的操作
		if($wxpay->trade_type() == 'miniprogram'){
			$wxpay->trade_type('jsapi');
			$info = $wxpay->create($data);
			if(!$info){
				$app->error('支付出错，请联系管理员');
			}
			$app->assign('info',$info);
			$app->assign('data',$data);
			$app->assign('order',$this->order);
			$app->assign('price_rmb',$price);
			$config = $wxpay->get_jsapi_param($info);
			$app->assign('wxconfig',$config);
			$ajaxurl = $app->url('payment','query','sn='.$this->order['sn'].'-'.$this->order['id'],'api');
			$app->assign('ajaxurl',$ajaxurl);
			if($json){
				$array = array('appId'=>$config['appId']);
				$array['timeStamp'] = $config['timeStamp'];
				$array['nonceStr'] = $config['nonceStr'];
				$array['paySign'] = $config['paySign'];
				$array['logId'] = $order['id'];
				$array['order_id'] = $rs['id'];
				$array['snId'] = $this->order['sn'].'-'.$this->order['id'];
				$array['prepay_id'] = $info['prepay_id'];
				$app->success($array);
			}
			$app->tpl->display("payment/wxpay/submit_miniprogram");
			exit;
		}
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
		$ajaxurl = $app->url('payment','query','sn='.$this->order['sn'].'-'.$this->order['id'],'api');
		$app->assign('ajaxurl',$ajaxurl);
		//H5支付
		if($wxpay->trade_type() == 'mweb'){
			$notice_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
			$url = $info['mweb_url']."&redirect_url=".rawurlencode($notice_url);
			$app->_location($url);
			exit;
		}
		if($wxpay->trade_type() == 'jsapi'){
			$config = $wxpay->get_jsapi_param($info);
			$app->assign('wxconfig',$config);
			$app->tpl->display("payment/wxpay/submit_jsapi");
		}elseif($wxpay->trade_type() == 'native'){
			$app->tpl->display('payment/wxpay/submit_qrcode');
		}elseif($wxpay->trade_type() == 'miniprogram'){
			$wxpay->trade_type('jsapi');
			$app->assign('wxconfig',$config);
			$app->tpl->display($app->tpl->dir_tplroot."payment/wxpay/submit_miniprogram.html",'abs-file');
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