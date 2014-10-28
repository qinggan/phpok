<?php
/*****************************************************************************************
	文件： payment/tenpay/notify.php
	备注： 订单异步通知处理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月2日
*****************************************************************************************/
error_reporting(E_ALL ^ E_NOTICE);
$root_dir = str_replace("\\","/",dirname(__FILE__))."/../../";
include_once($root_dir.'framework/libs/html.php');
$url = "api.php?c=payment&f=notify&sn=".rawurlencode($_REQUESET['priv1']);
if(isset($_POST))
{
	foreach($_POST AS $key=>$value)
	{
		$url .= "&".$key."=".rawurlencode($value);
	}
}
$cls = new html_lib();
$cls->get_content($url);
?>