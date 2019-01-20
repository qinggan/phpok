<?php
/**
 * CSS样式集合器，用于合并多个CSS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月20日
**/

error_reporting(E_ALL ^ E_NOTICE);
function_exists("ob_gzhandler") ? ob_start("ob_gzhandler") : ob_start();
header("Content-type: text/css; charset=utf-8");
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
$type = isset($_GET['type']) ? $_GET['type'] : 'default';
if(!$type || ($type && !in_array($type,array('default','admin','open')))){
	$type = 'default';
}

//后台首页涉及到的样式文件
$file = array();

//后台首页
if($type == 'admin'){
	$file[] = 'artdialog.css';
	$file[] = 'icomoon.css';
	$file[] = 'smartmenu.css';
	$file[] = 'admin.css';
}

//后台弹窗口
if($type == 'open'){
	$file[] = 'icomoon.css';
	$file[] = 'open.css';
	$file[] = 'artdialog.css';
	$file[] = 'form.css';
	$file[] = 'smartmenu.css';
	//使用 selectpage 下拉菜单
	$file[] = 'selectpage.css';
}

//后台桌面窗口
if($type == 'default'){
	$file[] = 'admin-index.css';
	$file[] = 'window.css';
	$file[] = 'artdialog.css';
	$file[] = 'icomoon.css';
}

$file = array_unique($file);
foreach($file as $key=>$value){
	$value = basename($value);
	if(is_file(ROOT.$value)){
		echo file_get_contents(ROOT.$value);
		echo "\n";
	}
}
exit;