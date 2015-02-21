<?php
/*****************************************************************************************
	文件： tool.php
	备注： 方法比较
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月17日 14时59分
*****************************************************************************************/
error_reporting(E_ALL ^ E_NOTICE);
$dir1 = "framework/model/";
$dir2 = 'framework/model/admin/';
include_once('framework/libs/file.php');
$file_obj = new file_lib();
function get_funclist($dir)
{
	global $file_obj;
	$list = $file_obj->ls($dir);
	if(!$list)
	{
		return false;
	}
	$rslist = array();
	foreach($list as $key=>$value)
	{
		if(!is_file($value))
		{
			continue;
		}
		$name = basename($value);
		$name = str_replace(array('.php','_model'),'',$name);
		$info = $file_obj->cat($value);
		//正则获取
		preg_match_all("/function\s+([a-zA-Z0-9\_]+)\((.*)\)/isU",$info,$matches);
		foreach($matches[1] as $k=>$v)
		{
			$rslist[$name][$v] = $matches[2][$k];
		}
	}
	return $rslist;
}
$list1 = get_funclist($dir2);
$list2 = get_funclist($dir1);
$isok = true;
foreach($list1 as $key=>$value)
{
	foreach($value as $k=>$v)
	{
		if($list2[$key][$k] && $list2[$key][$k] != $v)
		{
			echo 'error:'.$key.'_model.php -------- function '.$k.' param<br />';
			$isok = false;
		}
	}
}

if($isok)
{
	echo 'check true';
}
?>