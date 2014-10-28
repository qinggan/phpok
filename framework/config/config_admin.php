<?php
/***********************************************************
	Filename: phpok/config/config_admin.php
	Note	: 后台控制器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-17 15:36
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

//后台自动加载的JS，此处的JS对应的CSS，HTML及图片路径是相对于网站根目录
$config['autoload_js'] .= ',jquery.artdialog.js,jquery.global.js,global.admin.js,jquery.smartmenu.js';


$config["db"]["debug"] = false;

$config["engine"]["cache"]["status"] = false;


//后台登录界面皮肤更换，仅限登录界面
$config['admin_tpl_login'] = 'login';