<?php
/**
 * PHPOK框架入口引挈文件，请不要改动此文件
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月21日
**/

/**
 * 安全限制
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 强制使用UTF-8编码
**/
header("Content-type: text/html; charset=utf-8");
header("Cache-control: no-cache,no-store,must-revalidate,max-age=3");
header("Pramga: no-cache"); 
header("Expires: -1");

if(!defined("ROOT")){
	define("ROOT",str_replace("\\","/",dirname(__FILE__))."/../");
}
if(!defined("FRAMEWORK")){
	define("FRAMEWORK",ROOT."framework/");
}

/**
 * 计算执行的时间
 * @参数 $is_end 布尔值
 * @返回 参数为true时返回执行的时间，为false定义常量 SYS_TIME_START 为当前时间
**/
function run_time($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_TIME_START")){
			return false;
		}
		define("SYS_TIME_START",microtime(true));
	}else{
		if(!defined("SYS_TIME_START")){
			return false;
		}
		return round((microtime(true) - SYS_TIME_START),5);
	}
}

/**
 * 登记内存
 * @参数 $is_end 布尔值
 * @返回 参数为true时返回使用的内存值，为false定义常量 SYS_MEMORY_START 为当前内存值
**/
function run_memory($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_MEMORY_START") || !function_exists("memory_get_usage")){
			return false;
		}
		define("SYS_MEMORY_START",memory_get_usage());
	}else{
		if(!defined("SYS_MEMORY_START")){
			return false;
		}
		$memory = memory_get_usage() - SYS_MEMORY_START;
		//格式化大小
		if($memory <= 1024){
			$memory = "1KB";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)."KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)."MB";
		}
		return $memory;
	}
}

run_time();
run_memory();

//定义PHP的版本
if (!defined('PHP_VERSION_ID')) {
    $php_version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
}

/**
 * 用于调试统计时间，无参数，启用数据库调试的结果会在这里输出，需要在模板适当位置写上：{func debug_time} 
**/
function debug_time()
{
	global $app;
	$time = run_time(true);
	$memory = run_memory(true);
	$sql_db_count = $app->db->sql_count();
	$sql_db_time = $app->db->sql_time();
	$cache_count = $app->cache->count();
	$cache_time = $app->cache->time();
	$string = '运行 {total} 秒，内存使用 {mem_total}，数据库执行 {sql_count} 次，';
	$string.= '用时 {sql_time} 秒，缓存执行 {cache_count} 次，用时 {cache_time} 秒';
	$array = array('total'=>$time,'mem_total'=>$memory);
	$array['sql_count']= $app->db->sql_count();
	$array['sql_time'] = $app->db->sql_time();
	$array['cache_count'] = $app->cache->count();
	$array['cache_time'] = $app->cache->time();
	$string = P_Lang($string,$array);
	return $string;
}

include FRAMEWORK.'_init_phpok.php';
include FRAMEWORK.'_init_auto.php';
include FRAMEWORK.'_init_lib.php';

/**
 * 安全注销全局变量
**/
unset($_ENV, $_SERVER['MIBDIRS'],$_SERVER['MYSQL_HOME'],$_SERVER['OPENSSL_CONF'],$_SERVER['PHP_PEAR_SYSCONF_DIR'],$_SERVER['PHPRC'],$_SERVER['SystemRoot'],$_SERVER['COMSPEC'],$_SERVER['PATHEXT'], $_SERVER['WINDIR'],$_SERVER['PATH']);

if(function_exists('mb_internal_encoding')){
	mb_internal_encoding("UTF-8");
}

$sapi_type = php_sapi_name();
if(isset($sapi_type) && substr($sapi_type, 0, 3) == 'cli'){
	$app = new _init_phpok(true);
	$tmp = $argv;
	unset($tmp[0]);
	if(!$tmp){
		echo 'no control'.PHP_EOL;
		exit;
	}
	$string = implode("&",$tmp);
	parse_str($string,$_POST);
}else{
	$app = new _init_phpok(false);
	$app->init_site();
}
include_once($app->dir_phpok."phpok_helper.php");
$app->init_view();

/**
 * 引用全局 app
**/
function init_app(){
	return $GLOBALS['app'];
}

/**
 * 核心函数，phpok_head_js，用于加载自定义扩展中涉及到的js
**/
function phpok_head_js()
{
	$debug = $GLOBALS['app']->config['debug'];
	$jslist = $GLOBALS['app']->jslist;
	if(!$jslist || !is_array($jslist)){
		return false;
	}
	$jslist = array_unique($jslist);
	$html = "";
	foreach($jslist as $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<script type="text/javascript" src="'.$value.'" charset="utf-8"></script>'."\n";
	}
	return $html;
}

/**
 * 核心函数，phpok_head_css，用于加载自定义扩展中涉及到的css
**/
function phpok_head_css()
{
	$debug = $GLOBALS['app']->config['debug'];
	$csslist = $GLOBALS['app']->csslist;
	if(!$csslist || !is_array($csslist)){
		return false;
	}
	$csslist = array_unique($csslist);
	$html = "";
	foreach($csslist as $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<link rel="stylesheet" type="text/css" href="'.$value.'" charset="utf-8" />'."\n";
	}
	return $html;
}

/**
 * 语言包变量格式化，$info将转化成系统的语言包，同是将$info里的带{变量}替换成$var里传过来的信息
 * @参数 $info 字符串，要替变的字符串用**{}**包围，包围的内容对应$var里的$key
 * @参数 $replace 数组，要替换的字符。
 * @返回 字符串，$info为空返回false
 * @更新时间 2016年06月05日
**/
function P_Lang($info,$replace='')
{
	$status = isset($GLOBALS['app']->config['multiple_language']) ? $GLOBALS['app']->config['multiple_language'] : false;
	if($status){
		return $GLOBALS['app']->lang_format($info,$replace);
	}
	if($replace && is_string($replace)){
		$replace  = unserialize($replace);
	}
	if($replace && is_array($replace)){
		foreach($replace as $key=>$value){
			$info = str_replace(array('{'.$key.'}','['.$key.']'),$value,$info);
		}
	}
	return $info;
}

/**
 * 核心函数，动态加CSS
**/
function phpok_add_css($file='')
{
	$GLOBALS['app']->addcss($file);
}

/**
 * 核心函数，动态加js
**/
function phpok_add_js($file='')
{
	$GLOBALS['app']->addjs($file);
}

/**
 * 执行动作
**/
$app->action();