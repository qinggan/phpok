<?php
/**
 * 订单异步通知处理
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月10日
**/

error_reporting(E_ALL ^ E_NOTICE);
define('PHPOK_SET',true);
$root_dir = str_replace("\\","/",dirname(__FILE__))."/../../../";
if(!isset($_POST)){
	exit('error');
}
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
include_once($root_dir.'framework/libs/html.php');
if($_POST['notify_type'] == 'batch_refund_notify'){
	$url = root_url()."api.php?c=order&f=refund&sn=".$_POST['batch_no'];
}else{
	$url = root_url()."index.php?c=payment&f=notify&sn=".rawurlencode($_POST['out_trade_no']);
}
foreach($_POST as $key=>$value){
	$url .= "&".$key."=".rawurlencode($value);
}
$cls = new html_lib();
echo $cls->get_content($url);