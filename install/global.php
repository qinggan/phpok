<?php
/***********************************************************
	Filename: install/global.php
	Note	: 安装程序包中涉及到的函数及对象
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//Get或是Post参数
//system参数为true时，则强制要求字母，数字，下划线及中划数
//为false仅过滤基本的引号信息
function G($id,$system=true)
{
	$msg = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : "");
	if($msg == '') return false;
	$msg = strtolower($msg);
	if($system)
	{
		if(!preg_match('/^[a-z][a-z0-9\_\-]+$/u',$msg))
		{
			error('参数不正确，请检查','','error');
		}
		return $msg;
	}
	else
	{
		$msg = stripslashes($msg);
		$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);
		return addslashes($msg);
	}
}

function error($tips="",$url="",$type="notice",$time=2)
{
	ob_end_clean();
	ob_start();
	//判断状态
	if(!$type || ($type && !in_array($type,array('notice','success','error')))) $type = 'notice';
	include(INSTALL_DIR."tpl/tips.php");
	exit;
}

function func_check($funName = '',$id='')
{
	return function_exists($funName) ? '支持' : '<span class="col_red">不支持</span>';
}

function format_sql($sql)
{
	global $db;
	$sql = str_replace("\r","\n",$sql);
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			$db->query($query);
		}
	}
}

function root_url()
{
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$port = $_SERVER["SERVER_PORT"];
	//$myurl = $http_type.$_SERVER["SERVER_NAME"];
	$myurl = $_SERVER["SERVER_NAME"];
	if($port != "80" && $port != "443")
	{
		$myurl .= ":".$port;
	}
	$site = array("domain"=>$myurl);
	$docu = $_SERVER["PHP_SELF"];
	$array = explode("/",$docu);
	$count = count($array);
	$dir = "";
	if($count>1)
	{
		foreach($array AS $key=>$value)
		{
			$value = trim($value);
			if($value)
			{
				if(($key+1) < $count)
				{
					$dir .= "/".$value;
				}
			}
		}
	}
	$dir .= "/";
	$dir = str_replace(array("//","install/"),array("/",""),$dir);
	$site["dir"] = $dir;
	return $site;
}


function connect_db($type="tpl",$url="")
{
	include(ROOT."config.php");
	$dbconfig = $config['db'];
	$db_name = "db_".$dbconfig['file'];
	$cls_sql_file = ROOT."framework/engine/db/".$dbconfig['file'].".php";
	if(!is_file($cls_sql_file))
	{
		if($type == "ajax") exit("数据库类文件：".$cls_sql_file."不存在！");
		error("数据库类文件：".$cls_sql_file."不存在！",$url,"error",5);
	}
	include($cls_sql_file);
	$db = new $db_name($dbconfig);
	//判断数据库连接正确与否
	if(!$db->conn_status())
	{
		if($type == "ajax") exit("数据库连接失败，请检查您的数据库信息本置。");
		error("数据库连接失败，请检查您的数据库信息本置。",$url,"error",5);
	}
	return $db;
}

//获取随机字串
function str_rand($length=10)
{
	$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$rand_str = '';
	for($i=0;$i<$length;++$i)
	{
		$rand_str .= $a[rand(0,61)];
	}
	return $rand_str;
}


error_reporting(E_ALL ^ E_NOTICE);
?>