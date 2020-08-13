<?php
/**
 * 订单异步通知处理网址
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年3月24日
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
$url = root_url()."index.php?c=payment&f=notify&sn=".rawurlencode($_POST['outTransNo']);
foreach($_POST as $key=>$value){
	$url .= "&".$key."=".rawurlencode($value);
}
$cls = new html_lib();
echo $cls->get_content($url);