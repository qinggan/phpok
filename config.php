<?php
/***********************************************************
	Filename: config.php
	Note	: 配置文件，此配置应用于全局，修改完后建议设为只读
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-15 15:46
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
// 连接数据库引挈，当前配置仅适用于小站点使用
$config["db"]["file"] = "mysqli";
$config["db"]["host"] = "127.0.0.1";
$config["db"]["port"] = "3306";
$config["db"]["user"] = "root";
$config["db"]["pass"] = "root";
$config["db"]["data"] = "phpok";
$config["db"]["prefix"] = "qinggan_";
$config['db']['socket'] = '';
$config["db"]["debug"] = false;
$config['db']['cache']['status'] = true;
$config['db']['cache']['type'] = 'file';
$config['db']['cache']['folder'] = ROOT.'data/cache/';
$config['db']['cache']['server'] = 'localhost';
$config['db']['cache']['port'] = 11211;
$config['db']['cache']['time'] = 86400; //Memcache限制不能超过30天，我们建议设置86400，一天


$config['mobile']['autocheck'] = true; //自动检测手机端，启用后，检测出手机端将读取手机端网页
$config['mobile']['status'] = true; //手机端开始，此项不开启的话，将不使用手机
$config['mobile']['default'] = false; //默认为手机版，为方便开发人员调式，设置为默认后，在网页上也会展示手机版
?>