<?php
error_reporting(E_ALL ^ E_NOTICE);
if(!isset($_GET['url'])){
	exit("Error");
}
$url = $_GET['url'];
if(!preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-\:\/\.]+$/u",$url)){
	exit("Error");
}
if(!isset($_GET['title'])){
	$info = parse_url($url);
	$title = $info['host'];
}else{
	$title = $_GET['title'];
}
if(!preg_match("/^[a-z0-9A-Z\_\-\.\x{4e00}-\x{9fa5}]+$/u",$title)){
	exit("Error4");
}
$Shortcut = "[InternetShortcut]
URL=".$url."
IDList=
IconFile=".$url."favicon.ico
IconIndex=1
[{000214A0-0000-0000-C000-000000000046}]
Prop3=19,2
";
header("Content-type: application/octet-stream");
$ua = $_SERVER["HTTP_USER_AGENT"];
if(preg_match("/MSIE/", $ua)){
	header('Content-Disposition: attachment; filename="'.rawurlencode($title).'.url"');
}else if(preg_match("/Firefox/", $ua)){
	header('Content-Disposition: attachment; filename*="utf8\'\''.$title.'.url"');
}else{
	header('Content-Disposition: attachment; filename="'.$title.'.url"');
}
echo $Shortcut;
?>