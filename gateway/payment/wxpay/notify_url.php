<?php
/*****************************************************************************************
	文件： payment/unionpay/notify_url.php
	备注： 订单异步通知处理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月24日 21时11分
*****************************************************************************************/
error_reporting(E_ALL ^ E_NOTICE);
define('PHPOK_SET',true);
$root_dir = str_replace("\\","/",dirname(__FILE__))."/../../../";
function root_url()
{
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$port = $_SERVER["SERVER_PORT"];
	$myurl = $_SERVER['SERVER_NAME'];
	if($port != "80" && $port != "443"){
		$myurl .= ":".$port;
	}
	$docu = $_SERVER["PHP_SELF"];
	if($_SERVER['PATH_INFO']){
		$docu = substr($docu,0,-(strlen($_SERVER['PATH_INFO'])));
	}
	$array = explode("/",$docu);
	$count = count($array);
	if($count>1){
		foreach($array AS $key=>$value){
			$value = trim($value);
			if($value){
				if(($key+1) < $count){
					$myurl .= "/".$value;
				}
			}
		}
		unset($array,$count);
	}
	$myurl .= "/";
	$myurl = str_replace("//","/",$myurl);
	return $http_type.$myurl.'../../../';
}
$info = file_get_contents("php://input");
if(!$info){
	exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[获取数据失败]]></return_msg></xml>');
}
include_once($root_dir.'framework/libs/xml.php');
$xml = new xml_lib();
$data = $xml->read($info,false);
if(!$data || $data['return_code'] != 'SUCCESS'){
	exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA['.$info['return_msg'].']]></return_msg></xml>');
}
include_once($root_dir.'framework/libs/html.php');
$url = root_url()."index.php";
$cls = new html_lib();
$cls->set_post(true);
echo $cls->get_content($url,'c=payment&f=notify&sn='.rawurlencode($data['out_trade_no']).'&xml='.rawurlencode($info));
exit;
?>